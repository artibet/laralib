<?php

namespace Artibet\Laralib;

use Illuminate\Support\ServiceProvider;
use Laralib\Console\Commands\MakePaginator;

class LaralibServiceProvider extends ServiceProvider
{
  // ---------------------------------------------------------------------------------------
  // boot
  // ---------------------------------------------------------------------------------------
  public function boot()
  {
    if ($this->app->runningInConsole()) {
      $this->commands([
        MakePaginator::class,
      ]);

      // Allow publishing of stubs
      $this->publishes([
        __DIR__ . '/Console/stubs/paginator.stub' => base_path('stubs/paginator.stub'),
      ], 'laralib-stubs');
    }
  }

  // ---------------------------------------------------------------------------------------
  // register
  // ---------------------------------------------------------------------------------------
  public function register()
  {
    //
  }
}
