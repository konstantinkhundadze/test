<?php

namespace base\model\entity;

class CustomerCardDetail extends EntityBase {

    public static $primaryKey = 'id';
    protected static $_modelName = 'base\model\CustomerRegCardDetail';
    protected static $_auxiliaryFields = array('check_expiry_date', 'cardNumber');

    /** @var int */
    public $id;

    /** @var int */
    public $customer_reg_id;

    /** @var string */
    public $card_type;

    /** @var int */
    public $last4_digits;

    /** @var string */
    public $card_token;

    /** @var string */
    public $first_name;

    /** @var string */
    public $surname;

    /** @var string */
    public $postzip_code;

    /** @var string */
    public $address1;

    /** @var string */
    public $address2;

    /** @var string */
    public $city;

    /** @var string */
    public $country = '';

    /** @var string */
    public $state = '';

    /** @var string */
    public $expiry_date;

    /** @var int */
    public $status = 0;

    /** @var bool */
    public $barclays = 0;

    /** @var bool auxiliary field */
    public $check_expiry_date = false;

    /** @var int auxiliary field */
    public $cardNumber;

    protected static $_infoFields = array(
        'id' => array(),
        'customer_reg_id' => array(),
        'card_type' => array(),
        'card_type' => array(),
        'first_name' => array(),
        'surname' => array(),
        'expiry_date' => array(),
        'barclays' => array(),
    );

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

    protected function _afterLoad()
    {
        parent::_afterLoad();
        $this->check_expiry_date = $this->checkCardDateExpiry($this->expiry_date);
    }

    protected function _beforeSave()
    {
        $this->_checkBarclays();
        parent::_beforeSave();
    }

    public function checkCardDateExpiry($date)
    {
        if ((substr($date, 3) > date('y', time())) || (substr($date, 3) == date('y', time()) && substr($date, 0, 2) >= date('m', time()))) {
            return false;
        } else {
            return true;
        }
    }

    public function isActive()
    {
        $_modelName = static::$_modelName;
        return $this->status == $_modelName::CARD_STATUS_ACTIVE;
    }

    public function getCardCharge()
    {
        $_modelName = static::$_modelName;
        return $_modelName::getCardCharge($this->card_type);
    }

    private function _checkBarclays()
    {
        if ($this->cardNumber) {
            $this->barclays = self::isBarclays($this->cardNumber) ? 1 : 0;
        }
    }

    /**
     * 
     * @param string $cardNumber
     */
    public static function isBarclays($cardNumber)
    {
        $firs6digits = substr(preg_replace("/[^0-9]/", "",$cardNumber), 0, 6);
        return in_array($firs6digits, self::$_barclays);
    }

    private static $_barclays = array(
        489054,
        489055,
        484498,
        484499,
        465901,
        465902,
        453978,
        453979,
        465867,
        465922,
        465923,
        465858,
        465859,
        465911,
        465921,
        465860,
        465861,
        492828,
        492829,
        492822,
        492823,
        484078,
        492901,
        492902,
        492906,
        492910,
        492912,
        492913,
        492914,
        492928,
        492929,
        492930,
        492937,
        492938,
        492939,
        492950,
        492951,
        492952,
        492953,
        492954,
        492955,
        492956,
        492957,
        492958,
        492959,
        492960,
        492970,
        492971,
        492972,
        492973,
        492974,
        492975,
        492976,
        492977,
        492978,
        492979,
        492980,
        492981,
        492982,
        492983,
        492984,
        492985,
        492986,
        492987,
        492988,
        492989,
        492990,
        492991,
        492992,
        492993,
        492994,
        492995,
        492996,
        492997,
        492998,
        492999,
    );

}
