<?php

namespace base\model\entity;

class CustomerRegAccount extends EntityBase {

    protected static $_modelName = 'base\model\CustomerRegAccount';

    public static $primaryKey = 'id';

    public $id;

    /** @var string */
    public $first_name;

    /** @var string */
    public $last_name;

    /** @var string */
    public $email;

    /** @var string */
    public $password;

    /** @var bool */
    public $account_verified = 0;

    /** @var bool */
    public $mobile_verified = 0;

    /** @var string */
    public $customer_title;

    /** @var string */
    public $mobile_num;

    /** @var string */
    public $passw_reset_hash = '';

    /** @var DateTime */
    public $last_updated;

    /** @var DateTime */
    public $last_login;

    /** @var int */
    public $success_logins_count = 0;
    
    /** @var int */
    public $unsuccess_logins_count = 0;

    /** @var string */
    public $registration_date;

    /** @var int */
    public $web_use = 0;

    /** @var int */
    public $ios_use = 0;

    /** @var int */
    public $android_use = 0;
    
    /** @var string */
    public $aditional_info = '';
    
    /** @var int */
    public $receive_sms = 0;

    /** @var string */
    public $customer_based_in;

    /** @var string */
    public $where_heard_aboutus = '';

    /** @var int */
    public $status = 1; // active

    /** @var string */
    public $customer_ip;
    
    /** @var int */
    public $internal_view = 0;

    /** @var bool */
    public $full_view = 0;

    /** @var int */
    public $pho_id;

    /** @var int */
    public $braintree_id;

    protected static $_infoFields = array(
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'account_verified' => '',
        'customer_title' => '',
        'mobile_num' => '',
        'aditional_info' => '',
        'receive_sms' => '',
        'customer_based_in' => '',
        'where_heard_aboutus' => '',
        'status' => ''
    );
    
    protected function _beforeSave()
    {
        if (!$this->last_updated) {
            $this->last_updated = date(\base\model\ModelPDO::DATETIME_FORMAT);
        }
        if (!$this->last_login) {
            $this->last_login = date(\base\model\ModelPDO::DATETIME_FORMAT);
        }
        unset($this->registration_date);
        parent::_beforeSave();
    }

    public function infoArray()
    {
        $info = array();
        foreach ($this->toArray() as $k => $v) {
            if (isset(self::$_infoFields[$k])) {
                $info[$k] = $v;
            }
        }
        return $info;
    }

}
