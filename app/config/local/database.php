<?php

return array(

	/*
	|--------------------------------------------------------------------------
	| Database Connections
	|--------------------------------------------------------------------------
	|
	| Here are each of the database connections setup for your application.
	| Of course, examples of configuring each database platform that is
	| supported by Laravel is shown below to make development simple.
	|
	|
	| All database work in Laravel is done through the PHP PDO facilities
	| so make sure you have the driver for your particular database of
	| choice installed on your machine before you begin development.
	|
	*/

	'connections' => array(

		'mysql' => array(
			'driver'    => $_ENV['DB_DRIVER'] ? $_ENV['DB_DRIVER'] : 'mysql',
			'host'      => $_ENV['DB_HOST'] ? $_ENV['DB_HOST'] : 'localhost',
			'database'  => $_ENV['DB_NAME'] ? $_ENV['DB_NAME'] : 'tigapi',
			'username'  => $_ENV['DB_USER'] ? $_ENV['DB_USER'] : 'homestead',
			'password'  => $_ENV['DB_PASS'] ? $_ENV['DB_PASS'] : 'secret',
			'charset'   => 'utf8',
			'collation' => 'utf8_unicode_ci',
			'prefix'    => '',
		),

    /*
		'pgsql' => array(
			'driver'   => 'pgsql',
			'host'     => 'localhost',
			'database' => 'homestead',
			'username' => 'homestead',
			'password' => 'secret',
			'charset'  => 'utf8',
			'prefix'   => '',
			'schema'   => 'public',
		),
    */
	),

);
