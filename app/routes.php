<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{
	return View::make('hello');
});

// Version all API calls.
Route::group(array('prefix' => '1'), function() {
  Route::resource('users', 'UserController');
  Route::get('/login', 'UserController@login');
});
