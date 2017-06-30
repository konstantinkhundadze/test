<?php

namespace base\model\entity;

use base\model\entity\EntityEmpty;

class PromotionalCode extends EntityBase {

    public static $primaryKey = 'id';
    protected static $_modelName = 'base\model\PromotionalCode';

    /** @var int */
    public $id;

    public $code;
    public $discount;
    public $status;
    public $min_booking_value;
    public $location_name;
    public $long;
    public $lat;
    public $radius;
    public $to_check;
    public $from_check;
    public $message;
    public $unlimited;
    public $percent;
    public $universal_unlimited;

    public function calculateDiscount($amount)
    {
        $discount = $this->discount;
        if ($this->percent) {
            $discount = $amount * $this->percent / 100;
        }
        return $discount;
    }
}
