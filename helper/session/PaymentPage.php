<?php

namespace base\helper\session;

class PaymentPage extends SessionAbstract implements SessionInterface {
    public  $title;
    public  $firstname;
    public  $lastname;
    public  $customer_email;
    public  $confirm_email;
    public  $customer_tel;
    public  $customer_mobile_number;
    public  $trip_reason;
    public  $txt_alerts;
    public  $check_save_passenger_details;
    public  $where_heard_aboutus;
    public  $additional_information;
}