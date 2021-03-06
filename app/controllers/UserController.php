<?php

use Tig\Storage\User\UserRepository as User;
use Tig\UserHelper;

class UserController extends \BaseController {

  /**
   * @var Tig\Storage\User\UserRepository
   */
  private $user;

  /**
   * Mock api key/secret pairs.
   *
   * @var array
   */
  private $apiAuth;

  /**
   * Base URL path.
   */
  private $basePath;

  /**
   * Store PartnerID for the API partner's users
   */
  private $apiPartnerID;

  /**
   * Inject the UserRepository as $user.
   *
   * Inject the mock API auth filter.
   */
  public function __construct(User $user) {
    $this->user = $user;

    $this->basePath = Config::get('app.url', '');

    if (empty($this->basePath)) {
      $this->basePath = (Request::secure()) ? 'https://' : 'http://';
      $this->basePath .= Request::server('HTTP_HOST') . '/1/users';
    }

    $this->apiAuth = Config::get('app.tig_api_auth', array());

    $this->beforeFilter('@apiAuthFilter');
  }

  /**
   * Mock authentication filter: Make sure API requests include API key and
   * secret.
   *
   * @param Route $route
   * @param Request $request
   */
  public function apiAuthFilter($route, $request) {

    // Escape header input.
    $apiAppId = DB::connection()->getPdo()->quote(Request::header('X-TiG-Application-Id'));
    $apiKey = DB::connection()->getPdo()->quote(Request::header('X-TiG-REST-API-Key'));

    // Check the DB for this App ID & Key
    $apiValid = tigcache(
      "SELECT PartnerID FROM tig.APIkeys WHERE ID = {$apiAppId} AND APIKey = {$apiKey}",
      600, // Cache time
      1 // Assume single result
    );

    if (isset($apiValid['PartnerID'])) {
      $this->apiPartnerID = $apiValid['PartnerID'];
      Config::set('app.tig_partner_id', $this->apiPartnerID);
    }

    if (empty($apiAppId) || empty($apiKey) || !$apiValid) {
      $response = Response::json(array('error' => 'unauthorized'));
      $response->setStatusCode(401);
      return $response;
    }
  }

  /**
   * For update/show/delete, make sure user exists.
   *
   * @param int $id
   * @return boolean
   */
  public function checkForUser($id) {
    $user = $this->user->find($id);
    return !empty($user);
  }

  /**
   * For update, make sure user with given email exists.
   *
   * @param int $email
   * @return int|false
   *   User id or false.
   */
  public function getUserIdByEmail($email) {
    $user = $this->user->findOne('Email', $email);
    if (empty($user) || empty($user->UserID)) {
      return false;
    }
    return $user->UserID;
  }

  /**
   * Generic response for operations on a missing user.
   *
   * @return mixed
   */
  public function noSuchUserResponse() {
    $response = Response::json(
      array('error' => 'User does not exist')
    );
    $response->setStatusCode(404);
    return $response;
  }

  /**
   * 1.0: Display user list.
   *
   * Future: Display user list by PartnerID.
   *
   * @todo Implement PartnerID restriction
   * @return mixed
   */
  public function index() {
    // $users = $this->user->all();
    // return $users;

    return array('success' => 'yay ' . $this->apiPartnerID);
  }

  /**
   * Store a newly created user in storage.
   *
   * @return Response
   */
  public function store() {
    $user = $this->user->create(Input::all());

    // Validate user.
    if (!$user->hasErrors()) {

      // Process email opt-in if we have one
      if (Input::get('emailOpt') && Config::get('app.tig_process_optin')) {
        UserHelper::processEmailOptin($user->UserID);
      }

      // Because the legacy DB doesn't have a created_at column, we'll have to
      // supply that without persisting it.
      $objectInfo = array(
        'createdAt' => date(DATE_ISO8601),
        'objectId' => $user->id,
      );

      /** @var Symfony\Component\HttpFoundation\Response $response */
      $response = Response::json($objectInfo);
      $response->setStatusCode(201);
      $response->headers->add(array(
        'Location' => $this->getShowURL($user),
      ));
    } else {

      // Validation errors.
      $errorResponse = array(
        'error' => true,
        'error_messages' => $user->getErrors(),
      );
      $response = Response::json($errorResponse);

      // 422 Unprocessable Entity.
      $response->setStatusCode(422);
    }

    return $response;
  }

  /**
   * Given a User, return its REST URL.
   *
   * @param User $user
   * @return string
   */
  public function getShowURL($user) {
    return $this->basePath . '/' . $user->id;
  }

  /**
   * Display the specified user.
   *
   * @param  int|string $resource
   *   User id or user email.
   * @return Response
   */
  public function show($resource) {
    $id = $this->getUserIdByResource($resource);
    return $id ? $this->user->find($id) : $this->noSuchUserResponse();
  }

  /**
   * Update the specified user in storage.
   *
   * @param  int|string $resource
   *   User id or user email.
   * @return Response
   */
  public function update($resource) {
    if (!$id = $this->getUserIdByResource($resource)) {
      return $this->noSuchUserResponse();
    }

    $user = $this->user->update($id, Input::all());

    // Validate user.
    if (!$user->hasErrors()) {

      // The TiG DB doesn't store this info, so we generate it here to comply
      // with the API spec.
      $objectInfo = array(
        'objectId' => $id,
        'updatedAt' => date(DATE_ISO8601),
      );

      /** @var Symfony\Component\HttpFoundation\Response $response */
      $response = Response::json($objectInfo);

    } else {

      // Validation errors.
      $errorResponse = array(
        'error' => true,
        'error_messages' => $user->getErrors(),
      );
      $response = Response::json($errorResponse);

      // 422 Unprocessable Entity.
      $response->setStatusCode(422);
    }

    return $response;
  }

  /**
   * Remove the specified user from storage.
   *
   * @param  int $id
   * @return Response
   */
  public function destroy($id) {
    try {
      $this->user->delete($id);
    }
    catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      return $this->noSuchUserResponse();
    }

    return Response::json(
                   array('success' => 'User deleted')
    );
  }

  /**
   * Login handler.
   *
   * @return Response
   */
  public function login() {
    $email = Request::query('email');
    $password = Request::query('password');

    $authUser = $this->user->login($email, $password);

    if (!$authUser) {
      return $this->noSuchUserResponse();
    }

    $response = Response::json(
                        $authUser
    );

    return $response;
  }

  /**
   * Return user id by resource.
   *
   * @param  int|string $resource
   *   User id or user email.
   *
   * @return id|FALSE
   *   The user id if user found, otherwise FALSE.
   */
  private function getUserIdByResource($resource) {
    $id = false;
    try {
      if (is_numeric($resource)) {
        // Find by id.
        if ($this->checkForUser($resource)) {
          $id = $resource;
        }
      } else {
        // Find by email.
        $id = $this->getUserIdByEmail($resource);
      }
    } catch (Exception $e) {
      return false;
    }
    return $id;
  }

}
