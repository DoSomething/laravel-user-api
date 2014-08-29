<?php

/**
 * LOCAL SETTINGS
 *
 * Copy, rename to .env.ENVIRONMENT.php, and configure accordingly. For
 * local sandboxes, rename to .env.local.php. For production, rename to
 * .env.production.php.
 *
 * See http://laravel.com/docs/configuration#protecting-sensitive-configuration
 * for details.
 */

return array(

  'DB_DRIVER'   => 'mysql',
  'DB_HOST'     => 'localhost',
  'DB_USER'     => 'USER',
  'DB_PASS'     => 'PASS',
  'DB_NAME'     => 'NAME',

  'BASE_PATH'   => 'http://api.mydomain.com/1',

  'TIMEZONE'    => 'UTC',

  'IS_DEBUG'    => true,

  /**
   * TiG APPLICATION SETTINGS
   *
   * See config/app.php for verbose documentation.
   */

  /**
   * API authorization keys. This is a serialized string, because Laravel
   * doesn't handle arrays well in .env.php files.
   *
   * TODO: Use a managed data store for these.
   */
  'TIG_API_AUTH' => 'a:2:{s:6:"appid1";s:4:"key1";s:6:"appid2";s:4:"key2";}',

  'TIG_PROCESS_OPTIN' => true,
);
