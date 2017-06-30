<?php

namespace base\helper;

use base\model\Mco as McoModel;

class B2bCommonFuns {
    
    const LOGIN_COOKIE_NAME = 'cogoTripMCO';
    const DETAILS_COOKIE_NAME = 'cogoTripMCODetails';
    const LOGIN_PARAMS_COOKIE_NAME = 'cogoTripMCOLoginParas';
    
    const SAVE_MCO_COOKIE_DAYS = 7;

    /**
     * @param type $mco
     * @param type $staylogged
     * @param type $redirectUrl
     * @throws Exception
     */
    public static function afterMcoLogin($mco, $staylogged, $redirectUrl = '/')
    {
        if ($mco->registration_steps == McoModel::STEP_REGISTERED) {
            throw new \Exception(McoModel::EMAIL_NOT_VALIDATED_MESSAGE);
        } elseif ($mco->mco_status == McoModel::STATUS_BANNED || $mco->mco_status == McoModel::STATUS_DELETED) {
            throw new \Exception(McoModel::DEACTIVATED_MESSAGE);
        } else {
            self::set_mco_id_cookie($mco->mco_id, self::SAVE_MCO_COOKIE_DAYS);
            self::set_mco_details_cookie($mco->mco_id, $mco);
            self::set_mco_login_details_cookie($mco->username, $staylogged, self::SAVE_MCO_COOKIE_DAYS);
            return Registry::getApp()->getResponse()->redirect($redirectUrl);
        }
    }

    public static function mcoLoginFailure($username, $password)
    {
        $data = array(
            'user_ip' => Registry::getApp()->getRequest()->getUserIP(),
            'user_agent' => Registry::getApp()->getRequest()->getUserAgent(),
            'username' => $username,
            'password' => $password
        );
        $model = new McoModel();
        $model->update_login_attempts($data);
    }

    /**
     * Function to get MCO ID from cookie
     * @return integer mco id
     */
    public static function get_mco_id_frm_cookie()
    {
        if (isset($_COOKIE[self::LOGIN_COOKIE_NAME])) {
            return base64_decode($_COOKIE[self::LOGIN_COOKIE_NAME]);
        }
        return '';
    }
    
    /**
     * Function to set MCO id in a cookie
     * @param integer $mco_id integer number mco id, pure as in database
     * @param integer $days integer number of days
     * this function encodes this mco id and this encoded mco id is kept in cookie
     */
    public static function set_mco_id_cookie($mco_id, $days = 0)
    {
        $encoded_mco_id = base64_encode($mco_id);
        if ($days > 0) {
            $time = time() + 60 * 60 * 24 * $days;
            setcookie(self::LOGIN_COOKIE_NAME, $encoded_mco_id, $time);
        } else {
            setcookie(self::LOGIN_COOKIE_NAME, $encoded_mco_id);
        }
    }

    /**
     * @todo remove $mcoId
     * Function to set MCO details in a cookie
     * @param integer $mcoId
     * @param \base\model\entity\Mco $mco
     */
    public static function set_mco_details_cookie($mcoId = 0, $mco = null)
    {
        $model = new McoModel();
        if ($mco) {
            $mco_details = $mco->decorate();
            $mco_name = $mco_details->registered_name;
            $mco_street = $mco_details->details->street;
            $mco_town = $mco_details->details->town;
            $mco_acc_id = $mco_details->username;
        } else {
            $mcoId = $mcoId ?: self::get_mco_id_frm_cookie();
            $mco_details = $model->get_mco_details($mcoId);
            $mco_name = $mco_details['registered_name'];
            $mco_street = $mco_details['street'];
            $mco_town = $mco_details['town'];
            $mco_acc_id = $mco_details['username'];
        }
        $mco_details = $mco_name . '^' . $mco_street . '^' . $mco_town . '^' . $mco_acc_id;

        setcookie(self::DETAILS_COOKIE_NAME, $mco_details);
    }

    /**
     * Function to save MCO user and keep me login option in a cookie
     * @param string $mco_user mco user name
     * @param integer $keep_me_login flag indicating whether user has selected keep me login option
     * @param integer $days integer number of days
     * this function encodes this mco id and this encoded mco id is kept in cookie
     */
    public static function set_mco_login_details_cookie($mco_user, $keep_me_login, $days = 0)
    {
        $encoded_mco_user = base64_encode($mco_user);
        $encoded_keep_me_login = trim(base64_encode($keep_me_login));

        $data = $encoded_mco_user . '^' . $encoded_keep_me_login;

        if ($days > 0) {
            $time = time() + 60 * 60 * 24 * $days;
            setcookie(self::LOGIN_PARAMS_COOKIE_NAME, $data, $time);
        } else {
            setcookie(self::LOGIN_PARAMS_COOKIE_NAME, $data);
        }
    }

}
