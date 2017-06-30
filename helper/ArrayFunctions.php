<?php

namespace base\helper;

class ArrayFunctions {

    /**
     * Aray trim and urldecode
     * @param array $array
     * @param bool $decode decode array values
     * @return void
     */
    public static function trimAray($array, $decode = false)
    {
        if (is_array($array)) {
            foreach ($array as &$value) {
                if (is_array($value)) {
                    $value = self::trimAray($value, $decode);
                } else {
                    $value = self::trimString($value, $decode);
                }
            }
        } elseif(is_string($array)) {
            $value = self::trimString($value, $decode);
        }
        return $array;
    }

    /**
     * String trim and urldecode
     * @param string $string
     * @param bool $decode decode array values
     * @return void
     */
    public static function trimString($string, $decode = false)
    {
        return $decode ? trim(urldecode($string)) : trim($string);
    }
}
