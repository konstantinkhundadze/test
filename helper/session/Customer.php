<?php

namespace base\helper\session;

class Customer extends SessionAbstract implements SessionInterface {

    public $id;
    public $first_name;
    public $last_name;
    public $email;
    public $mobile_num;
    public $customer_based_in;
    public $status;
    public $customer_title;
    public $braintree_id;
    public $pho_id;

}
