<?php

namespace Artibet\Laralib\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class MakeExport extends GeneratorCommand
{
  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'make:export';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Create a new excel export class';

  /**
   * The type of class being generated.
   *
   * @var string
   */
  protected $type = 'Export';

  /**
   * Get the stub file for the generator.
   *
   * @return string
   */
  protected function getStub()
  {
    $publishedStub = base_path('stubs/export.stub');
    if (file_exists($publishedStub)) {
      return $publishedStub;
    }
    return __DIR__ . '/../stubs/export.stub';
  }

  /**
   * Get the default namespace for the class.
   *
   * @param  string  $rootNamespace
   * @return string
   */
  protected function getDefaultNamespace($rootNamespace)
  {
    return $rootNamespace . '\Exports';
  }
}
