<?php

namespace base\helper;

class Geolocation {
    
    const UNIT_MI = 'mi';
    const UNIT_KM = 'km';

    public static function distanceByPoints($latitude1, $longitude1, $latitude2, $longitude2, $unit = self::UNIT_MI, $round = 0)
    {
        $theta = $longitude1 - $longitude2;
        $distance = (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($theta)));
        $distance = acos($distance);
        $distance = rad2deg($distance);
        $distance = $distance * 60 * 1.1515;
        if($unit == self::UNIT_KM) {
            $distance = $distance * 1.609344;
        }
        if ($round) {
            $distance = round($distance, 2);
        }
        return $distance;
    }

}
