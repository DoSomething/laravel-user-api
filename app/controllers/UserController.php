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

    $this->beforeFilter('@mockApiAuthFilter');
  }

  /**
   * Mock authentication filter: Make sure API requests include API key and
   * secret.
   *
   * @param Route $route
   * @param Request $request
   */
  public function mockApiAuthFilter($route, $request) {

    $apiAppId = Request::header('X-TiG-Application-Id');
    $apiKey = Request::header('X-TiG-REST-API-Key');

    if (empty($apiAppId) || empty($apiKey) ||
      empty($this->apiAuth[$apiAppId]) || $this->apiAuth[$apiAppId] != $apiKey
    ) {
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

    return array('success' => 'yay');
  }

  /**
   * Store a newly created user in storage.
   *
   * @return Response
   */
  public function store() {
    $user = $this->user->create(Input::all());

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
   * @param  int $id
   * @return Response
   */
  public function show($id) {

    try {
      return $this->user->find($id);
    }
    catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
      return $this->noSuchUserResponse();
    }
  }

  /**
   * Update the specified user in storage.
   *
   * @param  int $id
   * @return Response
   */
  public function update($id) {
    if (!$this->checkForUser($id)) {
      return $this->noSuchUserResponse();
    }

    $this->user->update($id, Input::all());

    // The TiG DB doesn't store this info, so we generate it here to comply
    // with the API spec.
    $objectInfo = array(
      'objectId' => $id,
      'updatedAt' => date(DATE_ISO8601),
    );

    /** @var Symfony\Component\HttpFoundation\Response $response */
    $response = Response::json($objectInfo);

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

}
