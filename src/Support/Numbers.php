<?php

namespace Artibet\Laralib\Support;

use NumberFormatter;

/*
    Number fromating and stuff ...
*/

class Numbers
{
  public static function formatCurrency($poso)
  {
    $fmt = new NumberFormatter('el_GR', NumberFormatter::CURRENCY);
    return $fmt->formatCurrency($poso, "EUR");
  }
}
