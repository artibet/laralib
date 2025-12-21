<?php

namespace Artibet\Laralib\Abstracts;

use PhpOffice\PhpWord\TemplateProcessor;

abstract class DocumentBase
{
  protected string $tempDir;

  // ---------------------------------------------------------------------------------------
  // Constructor
  // ---------------------------------------------------------------------------------------
  public function __construct()
  {
    // Temp dir to store processed docx document
    $this->tempDir = storage_path('app/temp');
    if (!file_exists($this->tempDir)) {
      mkdir($this->tempDir, 0777, true);
    }
  }

  // ---------------------------------------------------------------------------------------
  // Abstract overriden methods
  // ---------------------------------------------------------------------------------------
  protected abstract function templatePath(): string;
  protected abstract function basename(): string;
  protected abstract function build(TemplateProcessor $template): void;


  // ---------------------------------------------------------------------------------------
  // Download docx document
  // ---------------------------------------------------------------------------------------
  public function downloadDocx()
  {
    // Open template and set placeholder data
    $template = new TemplateProcessor($this->templatePath());
    $this->build($template);

    // Temp docx file
    $tempDocx = "{$this->tempDir}/" . $this->basename() . "_" . uniqid() . '.docx';
    $template->saveAs($tempDocx);

    // Return and delete
    $filename = $this->basename() . '.docx';
    return response()->download($tempDocx, $filename)->deleteFileAfterSend(true);
  }

  // ---------------------------------------------------------------------------------------
  // Download pdf document
  // ---------------------------------------------------------------------------------------
  public function downloadPdf()
  {
    // Set libreoffice (windows on linux)
    $soffice = stripos(PHP_OS, 'WIN') === 0
      ? 'soffice.exe'
      : 'libreoffice';

    // Open template and set placeholder data
    $template = new TemplateProcessor($this->templatePath());
    $this->build($template);

    // Temp docx file
    $tempDocx = "{$this->tempDir}/" . $this->basename() . "_" . uniqid() . '.docx';
    $template->saveAs($tempDocx);

    // Windows or linux
    if (stripos(PHP_OS, 'WIN') === 0) {
      $command = $soffice
        . ' --headless'
        . ' --convert-to pdf --outdir ' . escapeshellarg($this->tempDir)
        . ' ' . escapeshellarg($tempDocx);
      exec($command . ' 2>&1', $output, $returnVar);
      if ($returnVar !== 0) {
        return response()->json(['error' => 'PDF conversion failed.'], 500);
      }
    } else {
      // Create a libreoffice profile
      $userProfile = $this->tempDir . '/libreoffice_profile';
      if (!file_exists($userProfile)) {
        mkdir($userProfile, 0777, true);
      }

      // Convert docx to pdf using soffice
      $command = $soffice
        . ' --headless'
        . ' -env:UserInstallation=file:///' . escapeshellarg($userProfile)
        . ' --convert-to pdf --outdir ' . escapeshellarg($this->tempDir)
        . ' ' . escapeshellarg($tempDocx);
      exec($command . ' 2>&1', $output, $returnVar);
      if ($returnVar !== 0) {
        return response()->json(['error' => 'PDF conversion failed.'], 500);
      }
    }

    // Delete temporary .docx
    if (file_exists($tempDocx)) {
      unlink($tempDocx);
    }

    // Stream pdf and delete temp files
    $tempPdf = preg_replace('/\.docx$/i', '.pdf', $tempDocx);
    $filename = $this->basename() . '.pdf';
    return response()->download($tempPdf, $filename)
      ->deleteFileAfterSend(true);
  }
}
