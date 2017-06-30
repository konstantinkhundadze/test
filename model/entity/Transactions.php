<?php

namespace base\model\entity;

class Transactions extends EntityBase {

    public static $primaryKey = 'id';
    protected static $_modelName = 'base\model\Transactions';

    /** @var int */
    public $id;

    public $additional_information;
    public $AddressResult;
    public $AddressStatus;
    public $affiliate_id;
    public $amount;
    public $app_source;
    public $AVSCV2;
    public $billing_address_id;
    public $booking_completed_dts;
    public $booking_date;
    public $booking_fees;
    public $booking_id;
    public $booking_linktext;
    public $booking_status;
    public $cancellation_date;
    public $cancellation_requested_date;
    public $cancellation_source;
    public $cancelled_without_refund_wording;
    public $cansellation_reason;
    public $car_type;
    public $CardType;
    public $cc_charges;
    public $collect_eta_dts;
    public $customer_id;
    public $customer_reg_id;
    public $CV2Result;
    public $destination_address_id;
    public $destination_eta_dts;
    public $driver_id;
    public $driver_notes;
    public $driver_notification_status;
    public $driver_status_dt;
    public $flight_info;
    public $GiftAid;
    public $gross_amount;
    public $ip;
    public $is_dispute;
    public $is_pingit;
    public $journey_distance_km;
    public $journey_distance_miles;
    public $journey_et_ts;
    public $LastUpdated;
    public $luggage_json;
    public $mci_check;
    public $mco_id;
    public $notes;
    public $number_of_passengers;
    public $origination_device;
    public $paid;
    public $payment_id;
    public $payment_type;
    public $PayPalPayerID;
    public $PayerStatus;
    public $pickup_address_id;
    public $pickup_charge;
    public $pickup_eta_dts;
    public $pob_confirmed_dts;
    public $pob_confirmed_ip;
    public $pob_history;
    public $pob_linktext;
    public $PostCodeResult;
    public $price_inc_dec;
    public $price_inc_dec_type;
    public $price_type;
    public $promo_code_id;
    public $rate_cogotrip;
    public $rate_comments;
    public $rate_mco;
    public $refund_id;
    public $RelatedVendorTxCode;
    public $return_eta_dts;
    public $return_journey;
    public $search_address_log_id;
    public $search_log_id;
    public $SecurityKey;
    public $session_id;
    public $Status;
    public $suspicious_transaction;
    public $ThreeDSecureStatus;
    public $trip_charges;
    public $trip_reason;
    public $TxAuthNo;
    public $txt_alert;
    public $txt_alert_charges;
    public $type;
    public $user_agent;
    public $via_address_ids;
    public $VPSTxId;
    public $VendorTxCode;
    public $waiting_time;
}
