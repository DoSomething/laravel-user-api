<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Eloquent implements UserInterface, RemindableInterface {

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
   * The error messages from the last model validation.
   *
   * @var array
   */
  private $errors = array();

  /**
   * Model fields validation ruleset.
   *
   * @var array
   */
  private $rules = array(
    'Name'     => 'required',
    'Email'    => 'required|email|unique:Users',
    'Password' => 'required',
    'DOB'      => 'required|date',
  );

  /**
   * Add saving listener to attach model validation.
   */
  protected static function boot() {
    parent::boot();

    // Attach the validation on model saving.
    static::saving(function($model) {
      return $model->validate();
    });
  }

  /**
   * Validate model against specified ruleset.
   *
   * @return bool
   */
  public function validate()
  {
    $this->errors = array();
    $validator = Validator::make($this->attributes, $this->rules);

    if ($validator->fails()) {
      $this->errors = $validator->messages();
      return false;
    }

    return true;
  }

  /**
   * Return error messages for the last validation.
   *
   * @return array
   */
  public function getErrors() {
    return $this->errors;
  }

  /**
   * Determine if the model didn't pass the last validation.
   *
   * @return bool
   */
  public function hasErrors() {
    return !empty($this->errors);
  }

}
