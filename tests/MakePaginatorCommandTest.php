<?php

namespace Artibet\Laralib\Tests;

use Orchestra\Testbench\TestCase;
use Artibet\Laralib\LaralibServiceProvider;

class MakePaginatorCommandTest extends TestCase
{
  protected function getPackageProviders($app)
  {
    return [
      LaralibServiceProvider::class,
    ];
  }

  public function test_make_paginator_command(): void
  {
    $this->artisan('make:paginator TestPaginator')
      ->assertSuccessful();
  }
}
