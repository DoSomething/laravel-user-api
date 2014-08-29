<?php

namespace Tig\Storage\User;

use Illuminate\Support\Facades\DB as DB;
use Symfony\Component\CssSelector\Exception\SyntaxErrorException;
use User;
use Tig\UserHelper;


/**
 * Class EloquentUserRepository
 * @package Tig\Storage\User
 */
class EloquentUserRepository implements UserRepository {

  /**
   * Get all users.
   *
   * @return array
   */
  public function all() {
    return User::all();
  }

  /**
   * Find user by ID.
   *
   * @param int $id
   * @return User
   */
  public function find($id) {
    return $this->findOne('UserID', (int) $id);
  }

  /**
   * Given a key and value, find a single user.
   *
   * @param $key
   * @param $value
   * @return User
   */
  public function findOne($key, $value) {
    return User::where($key, '=', $value)->firstOrFail();
  }

  /**
   * Find by other search criteria (email, etc.)
   *
   * @param array $search
   * @return mixed
   */
  public function findBy($search) {
    // TODO: Implement findBy() method.
  }

  /**
   * Process & normalize user data for create or update. This should also
   * be a sanitizing layer to allow for mass-assignment in create() and
   * update().
   *
   * @param array $data Raw user data
   * @param string $method 'create' or 'update'
   * @throws \Exception
   * @return array            Normalized data
   */
  private function prepareUserData(array $data, $method = 'update')
  {
    // emailOpt doesn't affect this model, and is processed by the
    // controller.
    unset($data['emailOpt']);

    if ('create' == $method)
    {
      if (empty($data['password']))
      {
        throw new \Exception('Missing password');
      }

      // Let's have a username, hey.
      if (empty($data['username']))
      {
        $data['username'] = UserHelper::makeUsername($data);
      }

      $data['created_at'] = date('Y-m-d');

    }

    if (!empty($data['password']))
    {
      // SHA256 for new passwords.
      $data['password'] = UserHelper::makePassword($data['password']);
    }

    // Map Laravel-happy column names to their TiG DB equivalents.
    foreach ($data as $key => $val)
    {
      if (!empty(User::$columnMapping[$key]))
      {
        $data[User::$columnMapping[$key]] = $data[$key];
        unset($data[$key]);
      }
    }

    // We only ever expect to submit one email address.
    $data['PrimaryEmailID'] = 1;

    return $data;
  }

  /**
   * @param array $data
   * @throws Exception
   * @return User
   */
  public function create($data)
  {
    $data = $this->prepareUserData($data, 'create');

    $user = new User;

    $userID = $this->getNewId();

    $user->UserID = $userID;

    foreach ($data as $key => $val) {
      $user->$key = $val;
    }

    if ($user->save())
    {
      $user->id = $user->UserID = $userID;
      return $user;
    }

    throw new Exception(
      sprintf('Unable to create new user with email=%s', $data['Email'])
    );
  }

  /**
   * Update existing user.
   *
   * @param int $id
   * @param array $data
   * @throws \Exception
   * @return \User
   */
  public function update($id, $data)
  {
    $user = $this->find($id);

    $data = $this->prepareUserData($data, 'update');

    // TODO: Evaluate security of mass assignment.
    foreach ($data as $key => $val) {
      $user->$key = $val;
    }

    if ($user->save())
    {
      return $user;
    }

    throw new \Exception(
      sprintf('Unable to create new user with email=%s', $data['Email'])
    );
  }

  /**
   * Delete yourself.
   *
   * @param int $id
   */
  public function delete($id)
  {
    DB::table('Users')->where('UserID', '=', $id)->delete();
  }

  /**
   * Since the TiG UserID column doesn't autoincrement, find a new (max) user
   * ID for an insert.
   *
   * @return mixed
   */
  private function getNewId()
  {
    $maxIdRes = DB::table('Users')->select(DB::raw('max(UserID) as maxId'))->get();

    return (int) ++$maxIdRes[0]->maxId;
  }

  /**
   * Try login query with SHA256 password hash, which is the current practice
   * at TiG. Failing that, try MD5, the old hashing practice. Then give up.
   *
   * @param $email
   * @param $password
   * @return int|null
   */
  public function login($email, $password)
  {
    $res = DB::table('Users')
      ->select('UserID')
      ->where('email', '=', $email)
      ->where('password', '=', hash('sha256', $password))
      ->get();

    if (!empty($res[0]->UserID))
    {
      return $this->find($res[0]->UserID);
    }

    $res = DB::table('Users')
       ->select('UserID')
       ->where('email', '=', $email)
       ->where('password', '=', hash('md5', $password))
       ->get();

    if (!empty($res[0]->UserID))
    {
      return $this->find($res[0]->UserID);
    }

    return null;
  }
}
