<?php

namespace base\model\entity;

class Customer extends EntityBase {

    protected static $_modelName = 'base\model\Customer';

    public static $primaryKey = 'customer_id';

    public $customer_id;

    /** @var string */
    public $title;

    /** @var string */
    public $firstname;

    /** @var string */
    public $lastname;

    /** @var int */
    public $pickup_address_id;

    /** @var string */
    public $customer_email;

    /** @var string */
    public $customer_tel;

    /** @var string */
    public $customer_mobile_number;

    /** @var bool */
    public $accepted_tandcs = 0;

    /** @var string */
    public $where_heard_aboutus;

    /** @var int */
    public $customer_warning_level;

    /** @var string */
    public $customer_cogotrip_comments;

    /** @var int */
    public $customer_status;
    
    /** @var string */
    public $promocode;

}
