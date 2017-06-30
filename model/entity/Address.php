<?php

namespace base\model\entity;

class Address extends EntityBase {

    protected static $_modelName = 'base\model\Address';

    public static $primaryKey = 'address_id';

    public $address_id;

    /** @var string */
    public $organisation;

    /** @var string */
    public $property;

    /** @var string */
    public $street;

    /** @var int */
    public $locality;

    /** @var string */
    public $town;

    /** @var string */
    public $county;

    /** @var string */
    public $postcode;

    /** @var int */
    public $std_Code = 0;

    /** @var float */
    public $longitude = 0.0000000;

    /** @var float */
    public $latitude = 0.0000000;

    /** @var int */
    public $grid_north = 0;

    /** @var int */
    public $grid_east = 0;
    
    /** @var string */
    public $formatted_address;

    /** @var bool */
    public $card_payment_only = 0;
}
