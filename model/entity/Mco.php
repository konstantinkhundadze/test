<?php

namespace base\model\entity;

use base\model\entity\EntityEmpty;

class Mco extends EntityBase {

    public static $primaryKey = 'mco_id';
    protected static $_modelName = 'base\model\Mco';

    /** @var int */
    public $mco_id;

    public $account_type;
    public $pause_id;
    public $username;
//    public $password;
    public $registered_name;
    public $trading_name;
    public $operator_licence_number;
    public $local_authority;
    public $office_email;
    public $registered_address_id;
    public $office_contact_tel;
    public $mco_mobile_number;
    public $contact_person_day;
    public $contact_person_night;
    public $how_heard_aboutus;
    public $how_heard_aboutus_other;
    public $forms_sent_dts;
    public $forms_received_dts;
    public $mco_4seater_rate_per_mile;
    public $mco_6seater_rate_per_mile;
    public $mco_7seater_rate_per_mile;
    public $rate_per_hour;
    public $rate_return_discount;
    public $single_journey_radius;
    public $retrurn_journey_radius;
    public $backup_mco;
    public $backup_single_journey_radius;
    public $backup_retrurn_journey_radius;
    public $bank_sortcode;
    public $bank_name;
    public $bank_branch;
    public $bank_account_name;
    public $bank_account_number;
    public $first_pickup_ts;
    public $last_pickup_ts;
    public $mco_warning_level;
    public $mco_cogotrip_comments;
    public $registration_dt;
    public $mco_status;
    public $registration_steps;
    public $searches_since_launched;
    public $minimum_fare;
    public $licence_expiry_date;
    public $auth_name;
    public $auth_email;
    public $welcomepack_dts;
    public $fleet_size;
    public $do_not_send_customer_emails;
    public $minimum_booking_notice;
    public $fleet_label;
    public $seasonal_multiplier_id;
    public $accepted_payment_types;
    public $cash_payment_radius;
    public $first_visit;
    public $dispatch_system;
    public $website;
    public $estate_luggage_multiplayer;
    public $multiplayer_6;
    public $multiplayer_7;
    public $multiplayer_8;
    public $estate_luggage_multiplayer_type;
    public $multiplayer_type_6;
    public $multiplayer_type_7;
    public $multiplayer_type_8;
    public $price_additional_dropoff;
    public $pho_rating;
    public $min_fare_1_4;
    public $min_fare_1_4_e;
    public $min_fare_5_6;
    public $min_fare_7;
    public $min_fare_8;
    public $minimum_booking_notice_4;
    public $minimum_booking_notice_4e;
    public $minimum_booking_notice_6;
    public $minimum_booking_notice_7;
    public $minimum_booking_notice_8;
    
    public function load($data)
    {
        $this->_beforeLoad();

        $vars = call_user_func('get_object_vars', $this);

        foreach ($vars as $k => $v) {
            if (isset($data[$k]) && $this->validate($k, $v)) {
                $this->{$k} = $data[$k];
            } else {
                $kr = str_replace('mco_', '', $k);
                if (isset($data[$kr]) && $this->validate($k, $v)) {
                    $this->{$k} = $data[$kr];
                }
            }
        }

        $this->_afterLoad();

        return $this;
    }

}
