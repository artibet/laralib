<?php

namespace Artibet\Laralib\Abstracts;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

abstract class ExportBase
{

  // ---------------------------------------------------------------------------------------
  // Abstract overriden methods
  // ---------------------------------------------------------------------------------------
  protected abstract function templatePath(): string;
  protected abstract function filename(): string;
  protected abstract function build(Worksheet $sheet): void;

  // ---------------------------------------------------------------------------------------
  // Build and download the excel
  // ---------------------------------------------------------------------------------------
  public function download()
  {
    $spreadsheet = IOFactory::load($this->templatePath());
    $sheet = $spreadsheet->getActiveSheet();
    $this->build($sheet); // call overriden method
    return response()->streamDownload(function () use ($spreadsheet) {
      $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
      $writer->save('php://output');
    }, $this->filename());
  }
}
