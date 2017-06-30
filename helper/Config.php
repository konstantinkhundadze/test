<?php

namespace base\helper;

use base\exception\InvalidConfigException;

/**
 * @see https://minicabit.atlassian.net/browse/RT-876
 */
class Config {

    /**
     * @static
     * @var ConfigObject
     */
    private static $_config;

    /**
     * @static
     * @var array
     */
    private static $_configStore;

    /**
     * First priority
     * @static
     * @var string
     */
    private static $_configFile = '/../../config/config.ini';

    /**
     * Second priority
     * @static
     * @var string
     */
    private static $_configDevFile = '/../../config/config_dev.ini';

    public function __construct()
    {
        if (null === self::$_configStore) {
            if (!is_file(__DIR__ . self::$_configDevFile)) {
                throw new InvalidConfigException('"config_dev.ini" not found');
            }

            self::$_configStore = $this->_parseIni(__DIR__ . self::$_configDevFile, true);

            if (is_file(__DIR__ . self::$_configFile)) {
                $this->_mergeSettings($this->_parseIni(__DIR__ . self::$_configFile, true));
            }

            $this->_toObject(self::$_configStore);
        }
    }

    /**
     * @param type $section
     * @return type ConfigObject
     */
    public function __get($section)
    {
        return self::$_config->$section;
    }

    private function _toObject()
    {
        self::$_config = new ConfigObject();

        foreach (self::$_configStore as $section => $settings) {
            $sectionVars = new ConfigObject();

            foreach ($settings as $key => $param) {
                $sectionVars->$key = $param;
            }

            self::$_config->$section = $sectionVars;
        }

    }

    /**
     * Recursively merge settings
     *
     * @param type $settings
     * @throws InvalidConfigException
     */
    private function _mergeSettings($settings = array())
    {
        foreach ($settings as $section => $settings) {
            if (isset(self::$_configStore[$section])) {
                foreach ($settings as $key => $param) {
                    if (isset(self::$_configStore[$section][$key])) {
                        self::$_configStore[$section][$key] = $param;
                    } else {
                        throw new InvalidConfigException('Setting "' . $key . '" for section "' . $section . '" not found');
                    }
                }
            } else {
                throw new InvalidConfigException('Section "' . $section . '" not found');
            }
        }
    }

    public function _parseIni($file, $process_sections = false)
    {
        $explode_str = '.';
        $escape_char = "'";

        // load ini file the normal way
        $data = parse_ini_file($file, $process_sections);
        if (!$process_sections) {
            $data = array($data);
        }

        foreach ($data as $section_key => $section) {
            // loop inside the section
            foreach ($section as $key => $value) {
                if (strpos($key, $explode_str)) {
                    if (substr($key, 0, 1) !== $escape_char) {
                        // key has a dot. Explode on it, then parse each subkeys
                        // and set value at the right place thanks to references
                        $sub_keys = explode($explode_str, $key);
                        $subs = & $data[$section_key];
                        foreach ($sub_keys as $sub_key) {
                            if (!isset($subs[$sub_key])) {
                                $subs[$sub_key] = array();
                            }
                            $subs = & $subs[$sub_key];
                        }
                        // set the value at the right place
                        $subs = $value;
                        // unset the dotted key, we don't need it anymore
                        unset($data[$section_key][$key]);
                    }
                    // we have escaped the key, so we keep dots as they are
                    else {
                        $new_key = trim($key, $escape_char);
                        $data[$section_key][$new_key] = $value;
                        unset($data[$section_key][$key]);
                    }
                }
            }
        }
        if (!$process_sections) {
            $data = $data[0];
        }
        return $data;
    }

    /**
     * @return boolean debug.flag
     */
    public function isLocal()
    {
        return !!$this->debug->flag;
    }

}
