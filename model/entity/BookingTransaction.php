<?php

namespace base\model\entity;

class BookingTransaction extends EntityBase {

    public static $primaryKey = 'transaction_id';

    public $transaction_id;
        public $booking_id;
        public $payment_type;
        public $billing_address_id;
        public $mco_id;
        public $customer_id;
        public $affiliate_id;
        public $search_address_log_id;
        public $VendorTxCode;
        public $pickup_address_id;
        public $via_address_ids;
        public $pickup_eta_dts;
        public $journey_distance_km;
        public $journey_distance_miles;
        public $journey_et_ts;
        public $destination_address_id;
        public $destination_eta_dts;
        public $return_journey;
        public $waiting_time;
        public $number_of_passengers;
        public $amount;
        public $LastUpdated;
        public $booking_status;
        public $ip;
        public $user_agent;
        public $txt_alert;
        public $txt_alert_charges;
        public $type;
        public $gross_amount;
        public $trip_charges;
        public $cc_charges;
        public $booking_fees;
        public $collect_eta_dts;
        public $return_eta_dts;
        public $car_type;
        public $origination_device;
        public $additional_information;
        public $VPSTxId;
        public $SecurityKey;
        public $TxAuthNo;
        public $AVSCV2;
        public $AddressResult;
        public $PostCodeResult;
        public $CV2Result;
        public $GiftAid;
        public $ThreeDSecureStatus;
        public $CAVV;
        public $RelatedVendorTxCode;
        public $Status;
        public $AddressStatus;
        public $PayerStatus;
        public $CardType;
        public $PayPalPayerID;
        public $customer_reg_id;
        public $promo_code_id;
        public $luggage_json;
        public $price_type;
        public $pickup_charge;
        public $app_source;
        public $flight_info;
        public $is_pingit;

}
