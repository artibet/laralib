<?php

namespace Laralib\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class MakePaginator extends GeneratorCommand
{
  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'make:paginator';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Create a new paginator class';

  /**
   * The type of class being generated.
   *
   * @var string
   */
  protected $type = 'Paginator';

  /**
   * Get the stub file for the generator.
   *
   * @return string
   */
  protected function getStub()
  {
    $publishedStub = base_path('stubs/paginator.stub');
    if (file_exists($publishedStub)) {
      return $publishedStub;
    }
    return __DIR__ . '/../stubs/paginator.stub';
  }

  /**
   * Get the default namespace for the class.
   *
   * @param  string  $rootNamespace
   * @return string
   */
  protected function getDefaultNamespace($rootNamespace)
  {
    return $rootNamespace . '\Paginators';
  }
}
