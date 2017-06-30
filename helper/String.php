<?php

namespace base\helper;

use base\model\Booking;
use base\model\base\model;

class String {

    private static $_bookingModel;

    public static function camelize($word)
    {
        return lcfirst(preg_replace('/(^|_)([a-z])/e', 'strtoupper("\\2")', $word));
    }

    public static function uncamelize($camel, $splitter = '_')
    {
        return strtolower(preg_replace('/(?!^)[[:upper:]][[:lower:]]/', '$0', preg_replace('/(?!^)[[:upper:]]+/', $splitter . '$0', $camel)));
    }

    /**
     * parse bokins booking_linktext, VendorTxCode etc
     *
     * @param string $ref
     */
    public static function parseReference($ref)
    {
        $ex = explode('-', $ref);
        if (!empty($ex[0]) && intval($ex[0]) && !empty($ex[1]) && intval($ex[1])) {
            return trim($ex[0]) . '-' . trim($ex[1]);
        }
        return '';
    }

    /**
     * Strip HTML tags
     * @param string $text
     * @return string
     */
    public static function stripHTML($text)
    {
        $search = array(
            '@<script[^>]*?>.*?</script>@si', // Strip out javascript
            '@<[\/\!]*?[^<>]*?>@si', // Strip out HTML tags
            '@<style[^>]*?>.*?</style>@siU', // Strip style tags properly
            '@<![\s\S]*?--[ \t\n\r]*>@'
        ); // Strip multi-line comments including CDATA

        $text = preg_replace($search, '', $text);
        return $text;
    }

    /**
     * @param float $number
     * @return string -£0.00
     */
    public static function poundWitgSign($number)
    {
        return ($number < 0 ? '-' : '') . '&pound;' . sprintf('%01.2f', abs($number));
    }

}
