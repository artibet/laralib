<?php

namespace Artibet\Laralib\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class MakePaginator2 extends GeneratorCommand
{
  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'make:paginator2';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Create a new paginator class based on eloquest models';

  /**
   * The type of class being generated.
   *
   * @var string
   */
  protected $type = 'Paginator2';

  /**
   * Get the stub file for the generator.
   *
   * @return string
   */
  protected function getStub()
  {
    $publishedStub = base_path('stubs/paginator2.stub');
    if (file_exists($publishedStub)) {
      return $publishedStub;
    }
    return __DIR__ . '/../stubs/paginator2.stub';
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
