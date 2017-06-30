<?php

namespace base\helper;

use base\model\ModelPDO;

class DateTime {

    /**
     * @param int $minutes
     * @return string - 2 hours 40 mins
     */
    public static function minutesToDate($minutes, $format = 'H hours i mins')
    {
        return date($format, $minutes * 60);
    }

    /**
     * @param string $date
     * @param string $date2
     * @return \DateInterval
     */
    public static function diffDates($date, $date2)
    {
        $datetime1 = new \DateTime($date);
        $datetime2 = new \DateTime($date2);
        return $datetime1->diff($datetime2);
    }

    /**
     * @param \DateInterval $interval
     * @return int
     */
    public static function diffToSeconds(\DateInterval $interval)
    {
        return ($interval->y * 365 * 24 * 60 * 60) +
                ($interval->m * 30 * 24 * 60 * 60) +
                ($interval->d * 24 * 60 * 60) +
                ($interval->h * 60 * 60) +
                ($interval->i * 60) +
                $interval->s;
    }

    /**
     * @param \DateInterval $interval
     * @return int
     */
    public static function diffToMinutes(\DateInterval $interval)
    {
        return floor(self::diffToSeconds($interval) / 60);
    }

    public static function nicetime($date)
    {
        if (!$date) {
            return "No date provided";
        }
        $periods = array('second','min','hr','day','week','month','year','decade');
        $lengths = array('60','60','24','7','4.35','12','10');
        $now = time();
        $unix_date = strtotime($date);
        if (empty($unix_date)) {
            return "Bad date";
        }
        if ($now > $unix_date) {
            $difference = $now - $unix_date;
            $tense = "ago";
        } else {
            $difference = $unix_date - $now;
            $tense = "away";
        }
        for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j ++) {
            $difference /= $lengths[$j];
        }
        $difference = round($difference);
        if ($difference != 1) {
            $periods[$j] .= "s";
        }
        return "$difference $periods[$j] {$tense}";
    }

    /**
     * @todo
     * @param string $dateString
     * @param string $format
     * @return mixed
     */
    public static function fromMysqlFormat($dateString, $format = ModelPDO::DATETIME_FORMAT)
    {
        $dateString = trim($dateString);
        $ex1 = explode(' ', $dateString);
        if (count($ex1) == 2) {
            $ex2 = explode('-', $ex1[0]);
            if (count($ex2) == 3 && intval($ex2[0]) && intval($ex2[1]) && intval($ex2[2])) {
                $date = \DateTime::createFromFormat(ModelPDO::DATETIME_FORMAT, $dateString);
                return $date->format($format);
            }
        }
        return false;
    }

}
