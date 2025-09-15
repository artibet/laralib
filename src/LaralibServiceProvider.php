<?php

namespace Artibet\Laralib;

use Artibet\Laralib\Console\Commands\MakeExport;
use Artibet\Laralib\Console\Commands\MakePaginator;
use Illuminate\Support\ServiceProvider;

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
        MakeExport::class,
      ]);

      // Allow publishing of stubs
      $this->publishes([
        __DIR__ . '/Console/stubs/paginator.stub' => base_path('stubs/paginator.stub'),
        __DIR__ . '/Console/stubs/export.stub' => base_path('stubs/export.stub'),
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
