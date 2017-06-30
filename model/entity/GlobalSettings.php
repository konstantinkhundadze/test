<?php

namespace base\model\entity;

class GlobalSettings extends EntityBase {

    public static $primaryKey = 'id';

    protected static $_modelName = 'base\model\GlobalSettings';

    protected $_readOnly = true;

    /** @var int */
    public $id;

    /** @var string */
    public $setting_description;

    /** 
     * UNIQUE INDEX
     * @var string 
     */
    public $setting_name;

    /** @var string */
    public $value;

}
