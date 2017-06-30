<?php

namespace base\model;

use base\helper\Registry;

/**
 * @todo
 */
class CustomerRegAccount extends ModelPDO {

    protected static $_entityClass = 'base\model\entity\CustomerRegAccount';
    public static $table = 'customer_reg_account';
    private $pass_suffix = 'minicabit_tibacinim';

    const CLASS_NAME = __CLASS__;

    /**
     * @deprecated
     * @see base\model\Customer
     *
     * This function inserts in <b>customer table</b>
     * @param array $data post data customerinformation
     * @return integer PDO::lastInsertId()
     */
    public function savecustomerinfo($data)
    {
        $result = $this->insert(array(
            'title' => trim($data['title']),
            'firstname' => trim($data['firstname']),
            'lastname' => trim($data['lastname']),
            'customer_email' => trim($data['customer_email']),
            'customer_tel' => trim($data['customer_tel']),
            'customer_mobile_number' => trim($data['customer_mobile_number']),
            'where_heard_aboutus' => trim($data['where_heard_aboutus']),
            'accepted_tandcs' => trim($data['accepted_tandcs']),
            'promocode' => trim($data['promocode'])
                ), 'customer');

        return $result;
    }

    /**
     * @deprecated
     * @see base\model\Customer
     *
     * 'SELECT * FROM `customer` WHERE `customer_id` = '
     */
    public function getCustomerInfo($id)
    {
        return $this->findOne('SELECT * FROM `customer` WHERE `customer_id` = ?', $id);
    }

    /**
     * Return customer details using cutomer id
     * @param array
     */
    public function get_customer_reg_details($id)
    {
        $row = $this->find($id);
        return $row ? $row->toArray() : array();
    }

    /**
     * @deprecated
     * @see base\model\entity\Address
     */
    public function get_mco_address($id)
    {
        $sql = 'SELECT * FROM `address`,`mco` WHERE `address`.`address_id` = `mco`.`registered_address_id` AND `mco`.`mco_id` = ?';
        return $this->findOne($sql, $id);
    }

    public function android_requests($email)
    {
        return $this->insert(array('email' => trim($email)), 'android_requests');
    }

    /**
     * @todo
     */
    public function getFaforiteLocations($id)
    {
        $sql = 'SELECT * FROM `favorite_locations` WHERE `customer_reg_id` = ?';

        $r = $this->findRows($sql, $id);

        $switch = 0;
        foreach ($r as &$item) {
            if ($item['shift'] == 3) {
                $switch++;

                if ($switch > 1) {
                    $item['shift'] += $switch - 1;
                }
            }
        }
        return $r;
    }

    /**
     * @todo
     */
    public function deleteFaforiteLocation($id)
    {
        $sql = "DELETE FROM  `favorite_locations` WHERE  `id` = '" . $id . "'";
        return self::_getDB()->query($sql);
    }

    /**
     * @todo
     */
    public function updateFaforiteLocations($locations)
    {
        $result = self::_getDB()->insertNewRow('favorite_locations', $locations);
        return $status;
    }

    public function get_customer_reg_detail_by_email($email)
    {
        return $this->findOne($this->select()->where('email'), $email);
    }

    public function check_user_status_by_id($id)
    {
        $query = 'SELECT status FROM customer_reg_account WHERE  id = ?';
        return $this->fetchColumn($query, $id);
    }

    public function check_user_email_identyty($id, $email)
    {
        $query = 'SELECT * FROM customer_reg_account WHERE email = ? AND id = ?';
        return $this->findOne($query, array($email, $id));
    }

    public function add_customer_reg($data)
    {
        $data['password'] = $this->encode_login_password($data['password']);
        $data['customer_ip'] = Registry::getApp()->getRequest()->getUserIP();

        $customer = $this->_toObject($data)->save();

        $this->add_passanger_information_during_registration($data, $customer);

        return $customer->getPK();
    }

    public function add_passanger_information_during_registration($data, $customer = null)
    {
        $account = $customer ? $customer->toArray() : $this->get_customer_reg_detail_by_email($data['email']);
        $passanger_info = array(
            'customer_reg_account_id' => $account['id'],
            'first_name' => '',
            'last_name' => '',
            'email' => '',
            'mobile_num' => '',
            'receive_sms' => '1',
            'receive_newsletters' => $data['receive_newsletters'] ? $data['receive_newsletters'] : '1',
            'passenger_title' => $account['customer_title']
        );
        foreach ($passanger_info as $key => &$val) {
            if (!$val && isset($data[$key])) {
                $val = $data[$key];
            }
        }
        $this->insert($passanger_info, 'customer_reg_passengers');
    }

    public function encode_login_password($password)
    {
        return md5($password . $this->pass_suffix);
    }

    public function identify_customer_reg($email, $password)
    {
        $native_p = $password;
        $password = $this->encode_login_password($password);

        $query = 'SELECT * FROM customer_reg_account WHERE email = \'' . mysql_real_escape_string($email) . '\' AND password = \'' . mysql_real_escape_string($password) . '\'';
        $result = self::_getDB()->selectArray($query);

        if (!$result) {
            $password = \common_funs::simple_encode($native_p);
            $query = 'SELECT * FROM customer_reg_account WHERE email = \'' . mysql_real_escape_string($email) . '\' AND password = \'' . mysql_real_escape_string($password) . '\'';
            $result = self::_getDB()->selectArray($query);
        }

        return $result;
    }

    /**
     * @todo need check
     */
    public function customer_change_email($email, $id, $key)
    {
        $c_id = $id;
        if ($c_id) {
            $query = 'DELETE FROM customer_reg_change_email WHERE cust_id=\'' . mysql_real_escape_string($c_id) . '\'';
            self::_getDB()->query($query);
        }

        $query = 'INSERT INTO customer_reg_change_email (cust_id, code, email) VALUES (\'' . mysql_real_escape_string($id) . '\', \'' . mysql_real_escape_string($key) . '\' , \'' . mysql_real_escape_string($email) . '\' )';
        $result = self::_getDB()->query($query);
        return $result;
    }

    public function select_customer_change_email_by_id($id)
    {
        $query = 'SELECT * FROM customer_reg_change_email WHERE cust_id=\'' . mysql_real_escape_string($id) . '\'';
        $result = self::_getDB()->selectArray($query);
        return $result;
    }

    public function select_customer_change_email_by_code($code)
    {
        $query = 'SELECT * FROM customer_reg_change_email WHERE code=\'' . mysql_real_escape_string($code) . '\'';
        $result = self::_getDB()->selectArray($query);
        return $result;
    }

    public function delete_temp_email($email)
    {
        $sql = 'DELETE FROM customer_reg_change_email WHERE email=\'' . mysql_real_escape_string($email) . '\'';
        self::_getDB()->query($sql);
    }

    public function change_main_email($id, $email)
    {
        $sql = 'UPDATE customer_reg_account
			SET email = \'' . mysql_real_escape_string($email) . '\'
			WHERE id = ' . (int) $id . '';

        return self::_getDB()->query($sql);
    }

    public function check_email_exists($email)
    {
        $exists_email = self::_getDB()->selectRow('SELECT COUNT(*) FROM customer_reg_account WHERE email = \'' . mysql_real_escape_string($email) . '\'');
        return $exists_email[0];
    }

    public function isLogged()
    {
        if (isset($_SESSION['arrCustomer']['id'])) {
            if (check_session_login_expired()) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function getCustomerRegId()
    {
        if ($this->isLogged()) {
            $arrCustomer = $_SESSION['arrCustomer'];
            return $arrCustomer['id'];
        }
        return false;
    }

    public function add_verification_code($key, $id)
    {
        $sql = "INSERT INTO customer_reg_verification (customer_reg_id, verification_code) VALUES ('" . mysql_real_escape_string($id) . "',
		 '" . mysql_real_escape_string($key) . "')";

        $result = self::_getDB()->query($sql);

        return $result;
    }

    public function add_restore_code($key, $id)
    {
        self::_getDB()
                ->query('DELETE FROM customer_reg_restore_password WHERE cust_id = ' . mysql_real_escape_string($id) . '');

        self::_getDB()
                ->query("INSERT INTO customer_reg_restore_password (cust_id, code) VALUES ('" . mysql_real_escape_string($id) . "', '" . mysql_real_escape_string($key) . "')");
    }

    public function update_account_information($id, $arrData)
    {
        foreach ($arrData as $key => $val) {
            $val = \common_funs::html2txt($val);
            $arrData[$key] = mysql_real_escape_string($val);
        }
        self::_getDB()->updateWhere('customer_reg_account', $arrData, 'id = ' . mysql_real_escape_string($id));
        return true;
    }

    public function accept_new_password($data)
    {
        $sql = 'SELECT  date, cust_id  FROM customer_reg_restore_password WHERE code = "' . mysql_real_escape_string($data['key']) . '"';
        $result = self::_getDB()->selectArray($sql);

        $time = strtotime($result['date']);
        $id = $result['cust_id'];

        $password = $this->encode_login_password($data['password']);

        if ($time + 24 * 3600 > time()) {
            $sql = 'UPDATE customer_reg_account
			SET password = ("' . $password . '")
			WHERE id = ' . (int) $id . '';
            $result = self::_getDB()->query($sql);

            if ($result) {
                $sql = 'DELETE FROM customer_reg_restore_password WHERE cust_id = ' . (int) $id;
                if (self::_getDB()->query($sql)) {
                    return true;
                }
            }
        }
    }

    public function check_restore_code($key)
    {
        $sql = 'SELECT COUNT(id) FROM customer_reg_restore_password WHERE code = "' . mysql_real_escape_string($key) . '"';
        $result = self::_getDB()->selectRow($sql);
        return $result[0];
    }

    public function check_restore_code_change_email($key)
    {
        $sql = 'SELECT COUNT(id) FROM customer_reg_change_email WHERE code = "' . mysql_real_escape_string($key) . '"';
        $result = self::_getDB()->selectRow($sql);
        return $result[0];
    }

    public function check_verification($key)
    {
        $sql = 'SELECT customer_reg_id, generation_date FROM customer_reg_verification WHERE verification_code = "' . mysql_real_escape_string($key) . '"';
        $result = self::_getDB()->selectArray($sql);
        $r = $this->get_customer_reg_details($result['customer_reg_id']);
        return $r['account_verified'];
    }

    public function make_verification($key)
    {
        $sql = 'SELECT customer_reg_id, generation_date FROM customer_reg_verification WHERE verification_code = "' . mysql_real_escape_string($key) . '"';
        $result = self::_getDB()->selectArray($sql);

        $user_id = $result[0];
        $date_registration = $result[1];

        if (time() < strtotime($date_registration) + 24 * 3600) {
            $sql = 'UPDATE customer_reg_account
			SET account_verified = 1
			WHERE id = ' . (int) $user_id . '';

            $result = self::_getDB()->query($sql);

            return true;
        } else {
            return false;
        }
    }

    public function check_booking_equality_to_user($data, $id)
    {
        $sql = "SELECT customer.customer_email
			FROM booking,customer
			WHERE booking.customer_id = customer.customer_id
			AND booking.booking_linktext = '" . mysql_real_escape_string($data['ref']) . "'";
        $result = self::_getDB()->selectArray($sql);
        return (trim($result[0]));
    }

    /**
     * This function inserts Customer newsletters details
     * @param mixed $data post data customer newsletters information
     * @return boolean  value
     */
    public function savecustomernewsletter($data)
    {
        //Customer newsletters information
        $sql = "INSERT  INTO  customer_newsletter
                (email, customer_id, registered_dts, active, html_format)
                VALUES('" . mysql_real_escape_string($data['customer_email']) . "', '" . mysql_real_escape_string($data['customer_id']) . "',  now(), 1, 1)";

        $result = self::_getDB()->query($sql);

        return $result;
    }

    public function update_passenger_details($p_id, $arrData = array())
    {
        if (count($arrData) > 0 and isset($p_id)) {
            foreach ($arrData as $key => $val) {
                $val = \common_funs::html2txt($val);
                $arrData[$key] = mysql_real_escape_string($val);
            }
            self::_getDB()->updateWhere('customer_reg_passengers', $arrData, 'id = ' . $p_id);
            return true;
        }
    }

    public function get_customers_passenger_detail($c_id)
    {
        $sql = 'SELECT * FROM `customer_reg_passengers` WHERE `customer_reg_account_id` = ?';
        return $this->findOne($sql, $c_id);
    }

    public function read_passenger_by_email($email)
    {
        $sql = 'SELECT * FROM `customer_reg_passengers` WHERE `email` = ?';
        return $this->findOne($sql, $email);
    }

    public function login_mark($id)
    {
        if (isset($id) && !empty($id)) {
            self::_getDB()
                    ->query('UPDATE customer_reg_account SET last_login = NOW() WHERE id = ' . mysql_real_escape_string($id));
            return true;
        } else {
            return false;
        }
    }

}
