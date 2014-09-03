<?php

/**
 * @file UserHelper class declaration.
 */

namespace Tig;

use Illuminate\Support\Facades\DB as DB;

class UserHelper {

  private static $USERNAME_PREFIX = 'ds-';

  /**
   * Implement SHA256 hashing for new passwords.
   *
   * @param $raw
   * @return string
   */
  public static function makePassword($raw) {
    return hash('sha256', $raw);
  }

  /**
   * Try to use various properties to make a reasonable username for TiG.
   *
   * @param $data
   * @return string
   */
  public static function makeUsername($data) {

    if (!empty($data['mobile']))
    {
      return self::$USERNAME_PREFIX . preg_replace('/[^0-9]*/', '', $data['mobile']);
    }

    if (!empty($data['email']))
    {
      return self::$USERNAME_PREFIX . preg_replace('/[^a-z0-9A-Z]*/', '', $data['email']);
    }

    return substr(hash('sha256', time()), 16);
  }

  /**
   * Process email opt-in action by creating a row in UserEmailPrefs.
   *
   * @param int $id
   */
  public static function processEmailOptin($id)
  {
    DB::insert('INSERT INTO UserEmailPrefs (MemberID, flDispatch, flOpps)
                VALUES (?, 1, 1)', array($id));
  }

}
