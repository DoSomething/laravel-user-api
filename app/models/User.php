<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Way\Database\Model implements UserInterface, RemindableInterface {

	use UserTrait, RemindableTrait;

  protected $primaryKey = 'UserID';

  /**
   * Translation mapping of TiG column names to what we want the API to
   * support.
   *
   * @var array
   */
  public static $columnMapping = array(
    'id'          => 'UserID',
    'username'    => 'Username',
    'password'    => 'Password',
    'email'       => 'Email',
    'mobile'      => 'Phone',
    'firstName'   => 'Name',
    'ip'          => 'IP',
    'dob'         => 'DOB',
    'lat'         => 'GeoLat',
    'long'        => 'GeoLong',
    'city'        => 'City',
    'gender'      => 'Gender',
    'created_at'  => 'DateJoined',
  );

  /**
   * Mass-assignable properties.
   *
   * @var array
   */
  public $fillable = array(
    'Username',
    'Password',
    'Email',
    'Phone',
    'Name',
    'IP',
    'DOB',
    'GeoLat',
    'GeoLong',
    'City',
    'Gender',
    'DateJoined',
  );

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'Users';

  /**
   * We're inheriting a table that doesn't include updated_at and
   * created_at, so disable tracking.
   *
   * @var boolean
   */
  public $timestamps = false;

  /**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('Password', 'remember_token');

  /**
   * Model fields validation ruleset.
   *
   * @var array
   */
  protected static $rules = array(
    'Name'     => 'required',
    'Email'    => 'required|email|unique:Users',
    'Password' => 'required',
    'DOB'      => 'required|date',
  );

}
