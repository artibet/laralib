<?php

namespace Artibet\Laralib\Support;

use Carbon\Carbon;

/*
    Date fromating and stuff ...
*/

class Dates
{
  /**
   * Format date (iso string) to dd/mm/yyyy
   */
  public static function formatDate(string $isoString): string
  {
    if (!$isoString) return '';
    return (new Carbon($isoString))->setTimeZone('Europe/Athens')->format('d/m/Y');
  }

  /**
   * Format data (iso string) to hh:mm
   */
  public static function formatTime(string $isoString): string
  {
    if (!$isoString) return '';
    return (new Carbon($isoString))->setTimeZone('Europe/Athens')->format('H:i');
  }

  /**
   * Format date (iso string) to dd/mm/yyyy, hh:mm
   */
  public static function formatDateTime(string $isoString): string
  {
    if (!$isoString) return '';
    return (new Carbon($isoString))->setTimeZone('Europe/Athens')->format('d/m/Y, H:i');
  }


  /**
   * Return a translation of week day in greek
   */
  public static function getDay(Carbon $carbonDate, bool $upperCase = false, bool $article = false): string
  {
    if (!$carbonDate) return '';
    $dayOfWeek = $carbonDate->dayOfWeek;
    $day = '';
    $art = '';
    if ($dayOfWeek === 0) {
      $day = $upperCase ? 'ΚΥΡΙΑΚΗ' : 'Κυριακή';
      $art = $upperCase ? 'ΤΗΝ' : 'την';
    } else if ($dayOfWeek === 1) {
      $day = $upperCase ? 'ΔΕΥΤΕΡΑ' : 'Δευτέρα';
      $art = $upperCase ? 'ΤΗ' : 'τη';
    } else if ($dayOfWeek === 2) {
      $day = $upperCase ? 'ΤΡΙΤΗ' : 'Τρίτη';
      $art = $upperCase ? 'ΤΗΝ' : 'την';
    } else if ($dayOfWeek === 3) {
      $day = $upperCase ? 'ΤΕΤΑΡΤΗ' : 'Τετάρτη';
      $art = $upperCase ? 'ΤΗΝ' : 'την';
    } else if ($dayOfWeek === 4) {
      $day = $upperCase ? 'ΠΕΜΠΤΗ' : 'Πέμπτη';
      $art = $upperCase ? 'ΤΗΝ' : 'την';
    } else if ($dayOfWeek === 5) {
      $day = $upperCase ? 'ΠΑΡΑΣΚΕΥΗ' : 'Παρασκευή';
      $art = $upperCase ? 'ΤΗΝ' : 'την';
    } else if ($dayOfWeek === 6) {
      $day = $upperCase ? 'ΣΑΒΒΑΤΟ' : 'Σάββατο';
      $art = $upperCase ? 'ΤΟ' : 'το';
    }

    if ($article) return $art . ' ' . $day;
    else return $day;
  }

  /**
   * format protokollo from aa and date
   * $prot_date should be carbon
   */
  public static function formatProtokollo($prot_aa, $prot_date)
  {
    // $prot_date should be carbon
    if (!$prot_date) return '';
    return $prot_aa . "/" . $prot_date->day . "-" . $prot_date->month . "-" . $prot_date->year;
  }
}
