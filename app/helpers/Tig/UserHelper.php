<?php

/**
 * @file UserHelper class declaration.
 */

namespace Tig;

use Illuminate\Support\Facades\DB as DB;
use Illuminate\Support\Str as Str;

class UserHelper {

  private static $USERNAME_PREFIX = 'ds-';

  /**
   * Implement SHA256 hashing for new passwords.
   *
   * @param $raw
   * @return string
   */
  public static function makePassword($raw_password) {
    // SHA256 for new passwords.
    return hash('sha256', $raw_password);
  }

  /**
   * Sets hash method for the user password.
   *
   * @param $data
   * @return string
   */
  public static function setPasswordHashMethod(&$data) {
    $data['flSHA'] = 1;
  }

  /**
   * Return generated username based on first name and current time stamp
   * to avoid collisions.
   *
   * @param $data
   * @return string
   */
  public static function makeUsername($data) {
    return Str::lower($data['firstName']) . '-' . time();
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
