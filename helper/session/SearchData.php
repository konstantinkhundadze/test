<?php

namespace base\helper\session;

class SearchData extends SessionAbstract implements SessionInterface {

    public $num_of_passangers;
    public $c_to;
    public $c_from;
    public $date_to;
    public $date_from;
    public $luggage_weight;
    public $luggage_list;
    public $luggage_json;
    public $time_intervals;
    public $flight_infopublic;
    public $via;
    public $viaMiles;
    public $viaKm;
    public $day_of_travel;
    public $month_of_travel;
    public $pick_up_time_hr;
    public $pick_up_time_min;
    public $trip_type;
    public $journey_distance_km;
    public $journey_distance_miles;
    public $journey_time_millisec;
    public $journey_time_millisec_google;
    public $from_cat;
    public $to_cat;
    public $return_on_month;
    public $destination_time_hr;
    public $destination_time_min;
    public $amount;
    public $mco_id;
    public $mco_inbound;
    public $cap;
    public $capin;
    public $capout;
    public $return_journey;
    public $waiting_time;
    public $booking_fees;
    public $outbound_booking_fee;
    public $amount_inbound;
    public $amount_outbound;
    public $mco_outbound;
    public $payment_availability;
    public $gross_outbound_amount;
    public $gross_inbound_amount;
    public $driver_notice_timestamp;
    public $provider;
    public $is_affiliate;

    public function load($data)
    {
        foreach($data as $k => $v) {
            $data[str_replace('-', '_', $k)] = $v;
        }
        return parent::load($data);
    }

}
