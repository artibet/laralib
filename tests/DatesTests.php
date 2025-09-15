<?php

namespace Artibet\Laralib\Tests;

use Orchestra\Testbench\TestCase;
use Artibet\Laralib\Support\Dates;

class DatesTests extends TestCase
{
  // ---------------------------------------------------------------------------------------
  // formatDateTime - test iso string
  // ---------------------------------------------------------------------------------------
  public function test_iso_string_not_null()
  {
    $isoString = '2025-09-15T14:30:00Z'; // UTC datetime
    $result = Dates::formatDateTime($isoString);
    $this->assertEquals('15/09/2025, 17:30', $result);
  }

  // ---------------------------------------------------------------------------------------
  // formatDateTime - test null string
  // ---------------------------------------------------------------------------------------
  public function test_iso_string_is_null()
  {
    $isoString = null;
    $result = Dates::formatDateTime($isoString);
    $this->assertEquals('', $result);
  }
}
