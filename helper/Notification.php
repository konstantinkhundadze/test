<?php

namespace base\helper;

use base\helper\Registry;
use base\model\Booking;

class Notification {

    private $settings = array();

    /**
     * @var \base\model\Booking
     */
    private $_bookingModel;

    public function __set($name, $value)
    {
        $this->settings[$name] = $value;
    }

    public function install($data = array())
    {
        foreach ($data as $key => $value) {
            $this->settings[$key] = $value;
        }
        if ($data['data']['booking_affiliate_id']) {
            $this->_bookingModel = new Booking;
            $this->settings['data']['affiliate_url'] = $this->_bookingModel->get_footer_image($data['data']['booking_affiliate_id']);
        }
        return $this;
    }

    public function _new_send_confirmation_email()
    {
        $this->_send();
    }

    private function _send()
    {
        $data = $this->settings['data'];
        ob_start();
        require_once $_SERVER['DOCUMENT_ROOT'] . '/templates/emails/redesign/customer/letter.php';
        $result = ob_get_clean();
        $adminEmail = Registry::getConfig()->emails->admin;
        $infoEmail = Registry::getConfig()->emails->support;
        $trustpilotservice = Registry::getConfig()->emails->trustpilotservice;

        new \GenericEmail($this->settings['to'], 'minicabit booking confirmation', $result, $adminEmail, true, $infoEmail);

        if ($pickupDts = new \DateTime($data['booking_pickup_eta_dts'])) {
            if ($pickupDts->getTimestamp() > time() + 86400) {
                new \GenericEmail($trustpilotservice, 'minicabit booking confirmation', $result, $adminEmail, true, '');
            }
        }
    
        $this->settings = array();
    }

}
