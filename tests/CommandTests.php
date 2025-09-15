<?php

namespace Artibet\Laralib\Tests;

use Orchestra\Testbench\TestCase;
use Artibet\Laralib\LaralibServiceProvider;

class CommandTests extends TestCase
{
  protected function getPackageProviders($app)
  {
    return [
      LaralibServiceProvider::class,
    ];
  }

  // Test make:paginator
  public function test_make_paginator_command(): void
  {
    $this->artisan('make:paginator TestPaginator')
      ->assertSuccessful();
  }

  // Test make:export
  public function test_make_export_command(): void
  {
    $this->artisan('make:export TestExport')
      ->assertSuccessful();
  }
}
