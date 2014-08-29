<?php

namespace Tig\Storage;

use Illuminate\Support\ServiceProvider;

class StorageServiceProvider extends ServiceProvider {

  public function register()
  {
    $this->app->bind(
      'Tig\Storage\User\UserRepository',
      'Tig\Storage\User\EloquentUserRepository'
    );
  }

}
