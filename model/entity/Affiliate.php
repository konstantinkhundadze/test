<?php

namespace base\model\entity;

class Affiliate extends EntityBase {

    protected static $_modelName = 'base\model\Affiliate';

    public static $primaryKey = 'affiliate_id';

    public $affiliate_id;
    public $affiliate_name;
    public $affiliate_guid;
    public $tier1_count;
    public $tier1_reward;
    public $tier2_count;
    public $tier2_reward;
    public $tier3_count;
    public $tier3_reward;
    public $tier4_count;
    public $tier4_reward;
    public $tier5_count;
    public $tier5_reward;
    public $billing_period;
    public $payment_threshold;
    public $affiliate_address_id;
    public $contact_firstname;
    public $contact_lastname;
    public $contact_tel;
    public $contact_email;
    public $bank_sortcode;
    public $bank_name;
    public $bank_branch;
    public $bank_account_name;
    public $bank_account_number;
    public $background_image;
    public $header_image;
    public $header_alttext;
    public $redirect_url;
    public $footer_image;
    public $footer_image_url;
}
