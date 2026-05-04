<?php

namespace Artibet\Laralib\Support;

class GeoHelper
{
  /**
   * Υπολογίζει το offset μεταξύ εστίας και φωτογραφίας.
   */
  public static function distance(
    $lat1,
    $lng1,
    $lat2,
    $lng2,
    $refLat1 = 'N',
    $refLng1 = 'E',
    $refLat2 = 'N',
    $refLng2 = 'E'
  ): ?float {

    // Convert to decimals if coords are in degrees
    $cLat1 = self::convertToDecimal($lat1, $refLat1);
    $cLng1 = self::convertToDecimal($lng1, $refLng1);
    $cLat2 = self::convertToDecimal($lat2, $refLat2);
    $cLng2 = self::convertToDecimal($lng2, $refLng2);

    if ($cLat1 === null || $cLng1 === null || $cLat2 === null || $cLng2 === null) {
      return null;
    }

    $earthRadius = 6371000;

    $dLat = deg2rad($cLat1 - $cLat2);
    $dLng = deg2rad($cLng1 - $cLng2);

    $a = sin($dLat / 2) * sin($dLat / 2) +
      cos(deg2rad($cLat1)) * cos(deg2rad($cLat2)) *
      sin($dLng / 2) * sin($dLng / 2);

    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

    return round($earthRadius * $c, 2);
  }

  /**
   * Μετατρέπει DMS ή String σε Decimal.
   */
  public static function convertToDecimal($coord, $ref = 'N'): ?float
  {
    if ($coord === null) return null;

    $decimal = 0;

    if (is_numeric($coord)) {
      $decimal = (float)$coord;
    } elseif (is_array($coord) && count($coord) === 3) {
      $parts = [];
      foreach ($coord as $part) {
        // Διαχείριση του EXIF format "numerator/denominator"
        $data = explode('/', $part);
        if (count($data) === 2) {
          $parts[] = ($data[1] != 0) ? (float)$data[0] / (float)$data[1] : (float)$data[0];
        } else {
          $parts[] = (float)$part;
        }
      }
      $decimal = $parts[0] + ($parts[1] / 60) + ($parts[2] / 3600);
    } else {
      return null;
    }

    // Αν το Ref είναι South ή West, το πρόσημο γίνεται αρνητικό
    $ref = strtoupper($ref);
    return ($ref === 'S' || $ref === 'W') ? $decimal * -1 : $decimal;
  }
}
