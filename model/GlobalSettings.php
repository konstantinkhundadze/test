<?php

namespace base\model;

class GlobalSettings extends ModelPDO {

    public static $table = 'global_settings';

    protected static $_entityClass = 'base\model\entity\GlobalSettings';
    
    private static $_settings = null;

    /**
     * @return \base\model\entity\GlobalSettings collection
     */
    public function getSettings()
    {
        if (is_null(self::$_settings)) {
            if ($rows = self::$_db->fetchRows('SELECT * FROM `' . self::$table . '`')) {
                foreach ($rows as $row) {
                    self::$_settings[$row['setting_name']] = $this->_toObject($row);
                }
            }
        }
        return self::$_settings;
    }

    /**
     * @param string $name `setting_name`
     * @return \base\model\entity\GlobalSettings
     */
    public function getSetting($name = '')
    {
        $this->getSettings();
        return $name ? self::$_settings[$name] : self::$_settings;
    }
}
