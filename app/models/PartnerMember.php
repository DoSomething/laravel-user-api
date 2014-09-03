<?php

class PartnerMember extends Eloquent {

  protected $primaryKey = 'MemberID';

  /**
   * The database table used by the model.
   *
   * @var string
   */
  protected $table = 'PartnerMembers';

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
  protected $hidden = array();



}
