laravel-user-api
================

A generic, RESTful API built in Laravel and based on the [Parse User API](https://parse.com/docs/rest#users).

## Installation

1. Clone this repository into an environment with [PHP >= 5.4, the mcrypt extension](http://laravel.com/docs/installation#server-requirements), and [Composer](https://getcomposer.org/doc/00-intro.md#installation-nix).
2. Run `composer install` in the base directory.
3. Copy `env.DIST.php` to `.env.ENV.php`, where ENV is your [environment name according to Laravel](http://laravel.com/docs/configuration#environment-configuration). If in production, copy to `.env.php`.
4. Customize the `.env.ENV.php` or `.env.php` file to your requirements.
5. Make the `app/storage` directory writable by the user that owns your web server process.
6. Configure your web server's docroot to be the `public` directory.

## Database

Because this application implements [the Repository pattern](http://bit.ly/1vvu6wz) for access to user data, you can implement a new repository for your needs.
