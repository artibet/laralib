<?php

namespace Artibet\Laralib\Console\Commands;

use Illuminate\Console\GeneratorCommand;

class MakeDocument extends GeneratorCommand
{
  /**
   * The console command name.
   *
   * @var string
   */
  protected $name = 'make:document';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Create a new ms-word document export class';

  /**
   * The type of class being generated.
   *
   * @var string
   */
  protected $type = 'Document';

  /**
   * Get the stub file for the generator.
   *
   * @return string
   */
  protected function getStub()
  {
    $publishedStub = base_path('stubs/document.stub');
    if (file_exists($publishedStub)) {
      return $publishedStub;
    }
    return __DIR__ . '/../stubs/document.stub';
  }

  /**
   * Get the default namespace for the class.
   *
   * @param  string  $rootNamespace
   * @return string
   */
  protected function getDefaultNamespace($rootNamespace)
  {
    return $rootNamespace . '\Documents';
  }
}
