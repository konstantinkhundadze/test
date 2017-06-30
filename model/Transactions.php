<?php

namespace base\model;

use base\model\Customer as CustomerModel;
use base\model\entity\EntityEmpty;

class Transactions extends ModelPDO {

    protected static $_entityClass = 'base\model\entity\Transactions';
    public static $table = 'transactions';

    const TRIP_TYPE_SINGLE = 'SINGLE';
    const TRIP_TYPE_RETURN = 'RETURN';
    const TRIP_TYPE_INBOUND = 'INBOUND';
    const TRIP_TYPE_OUTBOUND = 'OUTBOUND';

    const PAMENT_TYPE_CARD = 'card';
    const PAMENT_TYPE_CASH = 'cash';
    
    const BOOKING_STATUS_NO_DRIVER = 'no_driver';
    const BOOKING_STATUS_DRIVER_ADDED = 'driver_added';
    const BOOKING_STATUS_DRIVER_EN_ROUTE = 'driver_en_route';
    const BOOKING_STATUS_CONFIRM_PICKUP = 'confirm_pickup';
    const BOOKING_STATUS_MCO_PAID = 'mco_paid';
    const BOOKING_STATUS_COMPLITED = 'completed';
    const BOOKING_STATUS_PENDING_CANCELATION = 'pending_cancellation';
    const BOOKING_STATUS_CANCELLED = 'cancelled';

    const DRIVER_NOTICE_STATUS_ = '';
    const DRIVER_NOTICE_STATUS_ASK_LIVE = 'ask_live';
    const DRIVER_NOTICE_STATUS_LIVE = 'live';
    const DRIVER_NOTICE_STATUS_NOT_LIVE = 'not_live';
    const DRIVER_NOTICE_STATUS_ASK_DISPATH = 'ask_driver_dispatch';
    const DRIVER_NOTICE_STATUS_DISPATH = 'driver_dispatch';
    const DRIVER_NOTICE_STATUS_NOT_DISPATH = 'driver_not_dispatch';
    const DRIVER_NOTICE_STATUS_ASK_POB = 'ask_pob';
    const DRIVER_NOTICE_STATUS_POB_3 = 'pob_confirmed_3';
    const DRIVER_NOTICE_STATUS_ENROUTE = 'driver_enroute';
    const DRIVER_NOTICE_STATUS_WAITING = 'waiting_passenger';
    const DRIVER_NOTICE_STATUS_PASSENGER_3 = 'passenger_not_show_3';
    const DRIVER_NOTICE_STATUS_NOT_DRIVER_3 = 'driver_not_show_3';
    const DRIVER_NOTICE_STATUS_OTHER_3 = 'other_3';
    const DRIVER_NOTICE_STATUS_POB_4 = 'pob_confirmed_4';
    const DRIVER_NOTICE_STATUS_PASSENGER_4 = 'passenger_not_show_4';
    const DRIVER_NOTICE_STATUS_NOT_DRIVER_4 = 'driver_not_show_4';
    const DRIVER_NOTICE_STATUS_OTHER_4 = 'other_4';

    /**
     * @param string $ref booking_linktext
     * @return \base\model\entity\Transactions
     */
    public function getByLinktext($ref)
    {
        $q = $this->select()->where('booking_linktext');
        $row = $this->findOne($q, $ref);
        return $row ? $this->_toObject($row) : null;
    }

    /**
     * @param string $VendorTxCode
     * @return \base\model\entity\Transactions
     */
    public function getByVendorTxCode($VendorTxCode, $tripType = '')
    {
        $params = array('VendorTxCode' => $VendorTxCode);
        $q = $this->select()->where('VendorTxCode', 'VendorTxCode');
        if ($tripType) {
            $q->where('type', 'type');
            $params['type'] = $tripType;
        }
        $row = $this->findOne($q, $params);
        return $row ? $this->_toObject($row) : null;
    }

    /**
     * @param int $mcoId
     * @param string $pickupFrom
     * @param string $pickupTo
     * @param int $limit
     * @param int $offset
     * @return array entity\Transactions colllection
     */
    public function getBookingsByMco($mcoId, $pickupFrom = '', $pickupTo = '', $limit = 0, $offset = 0, $customerName = '')
    {
        $params = array('mcoId' => $mcoId);
        $q = $this->select()
                ->calculateRows()
                ->columns(array(self::$table . '.*'))
                ->where(array('mco_id' => 'mcoId'))
                ->where('booking_id', '', '')
                ->order('pickup_eta_dts');
        if ($pickupFrom) {
            $q->where('pickup_eta_dts', 'pickupFrom', '>=');
            $params['pickupFrom'] = $pickupFrom;
        }
        if ($pickupTo) {
            $q->where('pickup_eta_dts', 'pickupTo', '<=');
            $params['pickupTo'] = $pickupTo;
        }
        if ($limit) {
            $q->limit((int)$limit, (int)$offset);
        }
        if (trim($customerName)) {
            $q->join(CustomerModel::$table, CustomerModel::$table.'.customer_id = '.self::$table.'.customer_id');
            $ex = explode(' ', trim($customerName));
            $params['fname'] = '%'.$ex[0].'%';
            $qCustomer = '(firstname LIKE :fname OR lastname LIKE :fname';
            if (isset($ex[1])) {
                $params['lname'] = '%'.$ex[1].'%';
                $qCustomer .= ' OR firstname LIKE :lname OR lastname LIKE :lname';
            }
            $qCustomer .= ')';
            $q->where($qCustomer, '', '');
        }
        $rows = $this->findRows($q, $params);
        return $this->_toCollection($rows);
    }

    public function getTripTopUp($booking_linktext)
    {
        $q = $this->select('booking_price_adjustments')
            ->columns(array('top_up' => 'sum(amount)'))
            ->where('booking_linktext');
        $row = $this->findOne($q, $booking_linktext);
        return $row ? $this->_toObject($row) : null;
    }

    public function analyseCustomer($customer_id)
    {
        $result = '';
        $sql = $this->select()
            ->columns(array(
                'cnt' => 'COUNT(booking_id)',
                'amount' => 'SUM(gross_amount)'
            ))
            ->where('customer_id')
            ->group('customer_id');
        if ($row = $this->findOne($sql, $customer_id)) {
            if ($row['cnt'] > 3 || ($row['cnt'] >= 2 && $row['amount'] >= 80)) {
                $result = 'g';
            } elseif (($row['cnt'] == 1 && $row['amount'] >= 80) || (($row['cnt'] == 3 || $row['cnt'] == 2) && $row['amount'] < 80)) {
                $result = 's';
            } elseif ($row['cnt'] == 1 && $row['amount'] < 80) {
                $result = 'b';
            }
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getLatest($mode = 'default')
    {
        $statusBooking = '';
        if ($mode == 'not_marked') {
            $statusCondition = 'AND b.driver_notification_status = "ask_live"';
        }
        if ($mode == 'not_live') {
            $statusCondition = 'AND b.driver_notification_status = "not_live"';
            $statusBooking = ' AND b.booking_status != "cancelled"';
        }

        if ($mode != 'default') {
            $sorting = 'b.pickup_eta_dts ASC';
            $statusCondition .= 'AND TIMESTAMPDIFF(SECOND,now(),b.pickup_eta_dts) > -3600';
            $mco = ' AND m.account_type != "test"';
        } else{
            $sorting = 'b.booking_date DESC';
        }

        $q = "
            SELECT b.* FROM ".self::$table." as b LEFT JOIN mco m ON b.mco_id = m.mco_id
            WHERE m.mco_id IS NOT NULL AND b.customer_id IS NOT NULL AND b.booking_id 
                $statusCondition
                $statusBooking
                $mco
            ORDER BY $sorting
            LIMIT 150";
        return $this->_toCollection($this->findRows($q));
    }

    /**
     * @todo
     * @return array
     */
    public function getSuspended()
    {
        $rows = array();
        $q = "
                SELECT b.booking_id, m.mco_id, m.mco_status, m.registered_name, m.trading_name, b.booking_linktext, b.pickup_eta_dts
                FROM mco AS m
                INNER JOIN transactions AS b ON m.mco_id = b.mco_id
                WHERE m.mco_status IN ('suspended','banned','locked','deleted')
                AND b.booking_id
                AND b.booking_status <> 'cancelled'
                AND b.pickup_eta_dts > NOW()
                ORDER BY pickup_eta_dts DESC
            ";

        return $this->findRows($q);
    }

    public function getConsole($limit = 0, $offset = 0, $options = array())
    {
        $params = array();
        $order = $options['order'] ?: 'b.pickup_eta_dts';
        $q = $this->select(array('b' => self::$table))
                ->calculateRows()
                ->where('b.booking_id', '', '')
                ->order($order);
        if ($limit) {
            $q->limit((int)$limit, (int)$offset);
        }
        if ($options['from']) {
            $q->where('b.pickup_eta_dts', 'pickupFrom', '>=');
            $params['pickupFrom'] = $options['from'];
        }
        if ($options['to']) {
            $q->where('b.pickup_eta_dts', 'pickupTo', '<=');
            $params['pickupTo'] = $options['to'];
        }
        if (($search_term = $options['search_term']) && ($search_value = $options['search_value'])) {
            if ($search_term == 'mco_username') {
                $q->join('mco', 'bk.mco_id = mco.mco_id')->where('mco.username', 'mcoUsername', 'LIKE');
                $params['mcoUsername'] = $search_value;
            } else if ($search_term == 'booking_ref') {
                $q->where('b.booking_linktext', 'linktext', 'LIKE');
                $params['linktext'] = $search_value;
            } else if ($search_term == 'cust_name') {
                $q->join(array('c' => 'customer'), 'bk.customer_id = c.customer_id')
                    ->where('(c.firstname LIKE :custumerName OR c.lastname LIKE :custumerName)', '', '');
                $params['custumerName'] = $search_value;
            } else if ($search_term == 'cust_email') {
                $sql = $sql . ' AND cust.customer_email LIKE  \'' . $search_value . '\'';
                $q->join(array('c' => 'customer'), 'bk.customer_id = c.customer_id')
                    ->where('c.customer_email', 'custumerEmail', 'LIKE');
                $params['custumerEmail'] = $search_value;
            } else if ($search_term == 'mco_id') {
                $q->where('b.mco_id', 'mcoId', 'LIKE');
                $params['mcoId'] = $search_value;
            } else if ($search_term == 'mco_name') {
                $q->join('mco', 'bk.mco_id = mco.mco_id')->where('mco.username', 'mcoRegname', 'LIKE');
                $params['mcoRegname'] = $search_value;
            } else if ($search_term == 'mco_licence_number') {
                $q->join('mco', 'bk.mco_id = mco.mco_id')->where('mco.operator_licence_number', 'mcoLicence', 'LIKE');
                $params['mcoLicence'] = $search_value;
            } else if ($search_term == 'mco_contact_name') {
                $q->join('mco', 'bk.mco_id = mco.mco_id')
                    ->where('(mco.contact_person_day LIKE :mcoContact OR mco.contact_person_night LIKE :mcoContact)', '', '');
                $params['mcoContact'] = $search_value;
            } else if ($search_term == 'mco_tele') {
                $q->join('mco', 'bk.mco_id = mco.mco_id')
                    ->where('(mco.office_contact_tel LIKE :mcoContactTel OR mco.contact_person_night LIKE :mcoContactTel)', '', '');
                $params['mcoContactTel'] = $search_value;
            }
        }
        if (!$options['show_all']) {
            $q->where("booking_status NOT IN ('completed', 'cancelled')", '', '');
        }
        return $this->_toCollection($this->findRows($q, $params));
    }

    public function get_ask_bookings($mco_id = null, $page = 1, $limit = 0, $ref = '')
    {
        $condition = $ref ? 'bk.booking_linktext = :ref' : 'bk.mco_id = :mcoId AND mco.mco_id = :mcoId';
        $sql = "SELECT SQL_CALC_FOUND_ROWS
                    CASE WHEN TIMESTAMPDIFF(minute,now(), pickup_eta_dts) > stage1_cutoff_interval AND booking_status = 'no_driver' THEN
                                1
                        WHEN TIMESTAMPDIFF(minute,now(), pickup_eta_dts) < stage1_cutoff_interval
                            AND TIMESTAMPDIFF(minute, pickup_eta_dts, now()) < 5
                            AND booking_status  IN('no_driver', 'driver_added') THEN
                                2
                        WHEN TIMESTAMPDIFF(minute, pickup_eta_dts, now()) > 5
                            AND TIMESTAMPDIFF(minute, pickup_eta_dts, now()) < 30
                            AND booking_status IN ('no_driver', 'driver_added', 'driver_en_route') THEN
                                3
                        WHEN TIMESTAMPDIFF(minute, pickup_eta_dts, now()) > 30
                        AND booking_status IN ('no_driver', 'driver_added', 'driver_en_route') THEN
                                4
                        ELSE NULL
                    END as stage_number,
                    data2.*
                FROM
                    (SELECT
                        CASE WHEN drive_time < single_journey_radius THEN
                            IF(min_cancellation_mins > 60, min_cancellation_mins, 60)
                            ELSE IF((60+drive_time) > min_cancellation_mins, 60+drive_time, min_cancellation_mins)
                        END as stage1_cutoff_interval,
                        data1.*
                    FROM
                            (SELECT
                                bk.id,
                                booking_linktext,
                                pickup_eta_dts,
                                booking_status,
                                bk.type as trip_type,
                                bk.is_dispute as is_dispute,
                                bk.via_address_ids as via_address_ids,
                                bk.payment_type as payment_type,
                                bk.number_of_passengers as number_of_passengers,
                                destadd.postcode as postcode_to,
                                custadd.postcode as postcoe_from,
                                destadd.county as country_to,
                                custadd.county as country_from,
                                destadd.property as property_to,
                                custadd.property as property_from,
                                destadd.town as town_to,
                                custadd.town as town_from,
                                destadd.locality as locality_to,
                                custadd.locality as locality_from,
                                destadd.street as street_to,
                                custadd.street as street_from,
                                driver_notification_status,
                                custadd.longitude as pickup_long,
                                custadd.latitude as pickup_lat,
                                mcoadd.longitude as mco_long,
                                mcoadd.latitude as mco_lat,
                                mco.single_journey_radius,
                                (distance(mcoadd.latitude, mcoadd.longitude, custadd.latitude, custadd.longitude) / 40)*60 as drive_time,
                                CASE WHEN mco.minimum_booking_notice <= 60 THEN
                                        CASE WHEN distance(mcoadd.latitude, mcoadd.longitude, custadd.latitude, custadd.longitude)  < mco.single_journey_radius THEN
                                                25
                                        ELSE
                                                25 + (distance(mcoadd.latitude, mcoadd.longitude, custadd.latitude, custadd.longitude) / 40)*60
                                        END
                                ELSE
                                        CASE WHEN distance(mcoadd.latitude, mcoadd.longitude, custadd.latitude, custadd.longitude)  < mco.single_journey_radius THEN
                                                165
                                        ELSE
                                                165 + (distance(mcoadd.latitude, mcoadd.longitude, custadd.latitude, custadd.longitude) / 40)*60
                                        END
                                END as min_cancellation_mins
                            FROM `" . self::$table . "` AS bk
                            INNER JOIN `mco` ON bk.mco_id = mco.mco_id
                            INNER JOIN `address` AS custadd ON custadd.address_id = bk.pickup_address_id
                            INNER JOIN `address` AS mcoadd ON mcoadd.address_id = mco.registered_address_id
                            INNER JOIN `address` AS destadd ON destadd.address_id = bk.destination_address_id
                            WHERE " . $condition . " AND booking_id 
                        ) as data1
                    ) as data2
            GROUP BY booking_linktext
            HAVING
                    driver_notification_status <> ''
                    AND CASE WHEN stage_number = 1 THEN driver_notification_status NOT IN ('', 'live', 'not_live')
                            WHEN stage_number = 2 THEN driver_notification_status NOT IN ('', 'driver_dispatch', 'driver_not_dispatch')
                            WHEN stage_number = 3 THEN driver_notification_status NOT IN ('', 'pob_confirmed_3', 'driver_enroute', 'waiting_passenger', 'passenger_not_show_3', 'driver_not_show_3', 'other_3')
                            WHEN stage_number = 4 THEN driver_notification_status NOT IN ('pob_confirmed_4', 'pob_confirmed_3', 'driver_not_show_3', 'driver_not_show_4', 'passenger_not_show_3', 'passenger_not_show_4', 'other_4', 'other_3', '')
                            ELSE FALSE
                        END
            ORDER BY pickup_eta_dts ASC";
        if ($ref) {
            return $this->findOne($sql, array('ref' => $ref));
        } else {
            if ($limit) {
                $record_first = ($page * $limit) - $limit;
                if ($record_first < 0) {
                    $record_first = 0;
                }
                $sql .= ' LIMIT ' . $record_first . ',' . $limit;
            }
            return $this->findRows($sql, array('mcoId' => $mco_id));
        }
    }

    /**
     * @todo
     * Function read booking details for given reference number.
     * @param string $mco_id MCO id
     * @param string $booking_ref Booking reference number.
     * @param boolean $current_trip It is boolean type input parameter. True value indicates search current trips.
     * Default value is false.
     * @param boolean $upcoming_trip It is boolean type input parameter. True value indicates seach
     * for upcomging trips.
     * @param string $show_all_upcoming if null then show top 5 upcoming trips
     * otherwise show all upcoming trips
     * Default value is false
     * @return mixed returns single or multiple records depending on user input
     */
    public function read_booking_by_reference($mco_id, $show_all_upcoming = false)
    {
        $booking_ref_clause = '';
        $current_trip_clause = '';
        $limit_clause = '';

        $upcomging_trip_clause = ' AND upcomging_trip = 1';
        $order_by_clause = ' Order by driver_flag asc, pickup_eta_dts asc ';
        //print $uc_all_upcoming_trips;die;
        if ($show_all_upcoming == true) {
            $limit_clause = ' ';
        } else {
            $limit_clause = ' limit 0, 5';
        }

        $sql = 'SELECT SQL_CALC_FOUND_ROWS booking_tbl.*  '
                . ' FROM ( SELECT '
                . ' bk.booking_id, bk.pickup_eta_dts, bk.additional_information, bk.payment_type, bk.promo_code_id, '
                . ' bk.number_of_passengers, bk.driver_id, '
                . ' bk.driver_status_dt, '
                . ' bk.car_type, '
                . ' bk.booking_fees, '
                . ' bk.booking_completed_dts, '
                . ' bk.is_dispute, '
                . ' bk.via_address_ids as via_address_ids, '
                . ' bk.driver_notes, '
                . ' bk.driver_notification_status,'
                . ' bk.paid,'
                . ' bk.is_pingit, bk.type AS booking_type,'
                . ' cust.customer_tel as customer_tel, '
                . ' cust.customer_mobile_number as customer_mobile, '
                . ' cust.customer_email as customer_email, '
                . ' amount, DATE_FORMAT(pickup_eta_dts, \'%d-%b-%Y\') as booking_date,'
                . ' booking_linktext as booking_ref,'
                . ' DATE_FORMAT(pickup_eta_dts, \'%d-%b-%Y\') as trip_date,'
                . ' DATE_FORMAT(pickup_eta_dts, \'%a %d %b\') as pickup_date,'
                . ' bk.pickup_address_id as From1,'
                . ' concat(IFNULL(pick.formatted_address,\'\')) as from_location, '
                . ' bk.destination_address_id  as To1,'
                . ' dest.formatted_address as dest_formatted_address, '
                . ' pick.formatted_address as pick_formatted_address, '
                . ' concat(IFNULL(dest.property,\'\'), \' \',  IFNULL(dest.street,\'\'), \' \', '
                . ' IFNULL(dest.locality,\'\'), \' \',  IFNULL(dest.town,\'\'), \' \',  '
                . ' IFNULL(dest.county,\'\'), \' \') as to_location, '
                . ' IF(return_journey=0,\'SNG\',\'RTN\') as trip_type,'
                . ' IF(return_journey=0,\'\', '
                . '        TIME_FORMAT( '
                . '             ADDTIME(destination_eta_dts, concat( FLOOR(waiting_time/60) '
                . '                    ,\':\',MOD(waiting_time, 60)'
                . '             ))'
                . '         ,\'%k:%i\')) as collection, '
                . ' IF(return_journey=0,\'\', TIME_FORMAT(ADDTIME('
                . ' ADDTIME(destination_eta_dts, concat( FLOOR(waiting_time/60) ,\':\',MOD(waiting_time, 60))), '
                . ' TIMEDIFF(destination_eta_dts, pickup_eta_dts )) ,\'%k:%i\')) as returntime, '
                . ' TIME_FORMAT(TIMEDIFF(destination_eta_dts,pickup_eta_dts), \'%k hour %i min (est.)\' ) as inbound, '
                . ' IF(return_journey=0,\'\', concat( FLOOR(waiting_time/60) ,\' hours \',MOD(waiting_time, 60), '
                . ' \' mins\')) as formated_waiting_time, '
                . ' DATE_FORMAT(pickup_eta_dts, \'%H:%i\') as pick_up_time, '
                . ' pick.town as from_town , dest.town as to_town, bk.return_journey, '
                . ' DATE_FORMAT(bk.pob_confirmed_dts, \'%H:%i:%s %d-%b-%Y\') as pob_confirmed_dts, '
                . ' bk.journey_distance_miles, '
                . ' DATE_FORMAT(pickup_eta_dts, \'%H:%i\') as start_time, '
                . ' DATE_FORMAT(destination_eta_dts, \'%H:%i\') as end_time, '
                . ' IF( bk.booking_status = \'completed\' OR bk.booking_status = \'cancelled\' ,\'\', '
                . ' concat(cust.firstname, \' \' , cust.lastname)) as main_passenger, '
                . ' amount as price_paid,'
                . ' trip_charges, '
                . ' price_inc_dec, '
                . ' price_inc_dec_type, '
                . ' IFNULL(concat(d.firstname, \' \' , d.lastname),\'--\')as driver_name,  '
                . ' TIMEDIFF(bk.pickup_eta_dts,now()) as time_to_pickup, '
                . ' if (return_journey = 0, '
                . '     destination_eta_dts,'
                . '    ADDTIME( '
                . '         ADDTIME( '
                . '                destination_eta_dts, '
                . '                concat( FLOOR(waiting_time/60) ,\':\',MOD(waiting_time, 60))'
                . '                ) '
                . '        , '
                . '        TIMEDIFF(destination_eta_dts, pickup_eta_dts) '
                . '        )) '
                . '    as destination_dttime , '
                . ' (pickup_eta_dts <= now() and '
                . ' if (return_journey = 0,destination_eta_dts,ADDTIME(ADDTIME(destination_eta_dts,concat( '
                . '      FLOOR(waiting_time/60) ,\':\',MOD(waiting_time, 60))),TIMEDIFF(destination_eta_dts, '
                . '     pickup_eta_dts))) '
                . ' >= now()) as current_trip, '
                . ' (pickup_eta_dts > now()) as upcomging_trip, '
                . ' IF(pob_confirmed_dts is null or pob_confirmed_dts = \'0000:00:00 00:00:00\', 0, 1) as pob_flag, '
                . ' IF(bk.driver_id is null or bk.driver_id = 0, 0, 1) as driver_flag, '
                . ' TIME_FORMAT(TIMEDIFF(destination_eta_dts,pickup_eta_dts),\'%H hours %i mins\' ) as total_job_time, '
                . ' TIME_FORMAT(TIMEDIFF('
                . ' ADDTIME('
                . ' ADDTIME(destination_eta_dts, concat( FLOOR(waiting_time/60) ,\':\',MOD(waiting_time, 60))), '
                . ' TIMEDIFF(destination_eta_dts, pickup_eta_dts )), pickup_eta_dts),\'%H hours %i mins\' ) as '
                . ' total_job_time_return, '
                . ' bk.txt_alert, bk.booking_status '
                . ' FROM `' . self::$table . '` bk '
                . ' LEFT JOIN driver d ON bk.driver_id= d.driver_id , '
                . ' customer cust, address pick, address dest '
                . ' WHERE bk.customer_id = cust.customer_id'
                . ' AND bk.mco_id = ' . $mco_id
                . ' AND bk.pickup_address_id = pick.address_id'
                . ' AND bk.destination_address_id = dest.address_id'
                . ' AND booking_id '
                . $booking_ref_clause
                . ' ) booking_tbl WHERE 1=1 '
                . $current_trip_clause
                . $upcomging_trip_clause
                . $order_by_clause
                . $limit_clause;

        return $this->findRows($sql, array('mcoId' => $mco_id));
    }

    /**
     * @param int $mco_id
     * @param string $from_date
     * @param string $to_date
     * @return array Entity colllection
     */
    public function get_all_first_bookings($mco_id, $from_date, $to_date)
    {
        $fields = 'b.*, p.formatted_address AS pickup_address, p.formatted_address AS dropoff_address, c.title, c.firstname, c.lastname';
        $join = '
            LEFT JOIN `customer`AS c ON c.`customer_id` = b.`customer_id`
            LEFT JOIN `address` AS p ON p.`address_id` = b.pickup_address_id
            LEFT JOIN `address` AS d ON d.`address_id` = b.destination_address_id
        ';

        $sql = "
            SELECT $fields FROM `booking_price_adjustments` a
            LEFT JOIN `transactions` b ON b.booking_linktext = a.booking_linktext
            LEFT JOIN `mco` m ON b.mco_id = m.mco_id
            $join
            WHERE a.deleted <> 1 
            AND CASE `b`.`payment_type` 
                WHEN 'card' THEN b.driver_notification_status IN ('pob_confirmed_4','pob_confirmed_3','driver_not_show_3','driver_not_show_4','passenger_not_show_3') AND b.is_dispute <> 1 
                WHEN 'cash' THEN b.driver_notification_status IN ('pob_confirmed_4','pob_confirmed_3','ask_live','live','driver_not_show_3','driver_not_show_4','passenger_not_show_3') AND b.booking_status <> 'cancelled' AND b.is_dispute <> 1 END 
            AND a.mco_id = :mco_id 
            AND CASE WHEN b.booking_completed_dts > :to_date THEN a.timestamp > '2094-08-24 23:59:59' 
                WHEN b.booking_completed_dts BETWEEN :from_date AND :to_date THEN a.timestamp < :to_date WHEN b.booking_completed_dts < :from_date THEN a.timestamp BETWEEN :from_date AND :to_date END 
            AND a.deleted <> 1 
            AND IF(b.mco_id = :mco_id, IF(b.booking_completed_dts IS NULL, b.pickup_eta_dts NOT BETWEEN :from_date AND :to_date, b.booking_completed_dts NOT BETWEEN :from_date AND :to_date), 1=1) 
            AND b.booking_status <> 'cancelled'
            GROUP BY b.booking_linktext 
            UNION ALL
            SELECT $fields FROM `booking_price_adjustments` a
            LEFT JOIN `transactions` b ON b.booking_linktext = a.booking_linktext
            LEFT JOIN mco m ON b.mco_id = m.mco_id
            $join
            WHERE a.deleted <> 1 
            AND a.mco_id = :mco_id 
            AND CASE WHEN b.cancellation_date > :to_date THEN a.timestamp > '2094-08-24 23:59:59' WHEN b.cancellation_date BETWEEN :from_date AND :to_date THEN a.timestamp < :to_date WHEN b.cancellation_date < :from_date THEN a.timestamp BETWEEN :from_date AND :to_date END 
            AND a.deleted <> 1 AND b.booking_status = 'cancelled' AND a.timestamp > b.cancellation_date
            GROUP BY b.booking_linktext
        ";
        
        $rows = $this->findRows($sql, array('mco_id' => $mco_id, 'from_date' => $from_date, 'to_date' => $to_date));
        return $this->_toCollection($rows, EntityEmpty::getClassName());
    }

    /**
     * @param int $mco_id
     * @param string $from_date
     * @param string $to_date
     * @return array Entity colllection
     */
    public function get_weekly_statement($mco_id, $from_date, $to_date)
    {
        $sql = "
            SELECT
            b.mco_id,
             a_b_owe.booking_linktext, SUM(book_owe) AS book_owe, SUM(adjust_owe) AS adjust_owe,
             b.payment_type,
             b.`type`,
             b.pickup_eta_dts,
             b.`trip_charges`,
             p_a.formatted_address AS pickup_address,
             d_a.formatted_address AS dropoff_address,
             c.firstname,
             c.lastname, CASE WHEN b.mco_id = a_b_owe.mco_id AND CASE `b`.`payment_type` WHEN 'card' THEN
            b.driver_notification_status IN ('pob_confirmed_4', 'pob_confirmed_3', 'driver_not_show_3', 'driver_not_show_4') AND b.is_dispute <> 1 AND b.booking_status <> 'cancelled' WHEN 'cash' THEN
            b.driver_notification_status IN ('pob_confirmed_4', 'ask_live', 'live', 'pob_confirmed_3', 'driver_not_show_3', 'driver_not_show_4') AND b.booking_status <> 'cancelled' AND b.is_dispute <> 1 END AND TIMESTAMPDIFF(MINUTE, `b`.`pickup_eta_dts`, NOW()) > 5 THEN CASE WHEN b.booking_status <> 'cancelled' THEN 'Completed ok' ELSE 'Cancelled' END WHEN b.mco_id <> a_b_owe.mco_id THEN 'Reallocated' ELSE '' END AS b_status
            FROM
            (
            SELECT b.mco_id,
             b.booking_linktext, CASE b.payment_type WHEN 'card' THEN IF((b.booking_status = 'cancelled' OR b.driver_notification_status IN ('driver_not_show_4', 'driver_not_show_3')), 0, FORMAT(IF(b.pickup_eta_dts > '2015-06-15 00:00:00',
             (b.trip_charges*0.88),
             (b.trip_charges*0.9)
            )
            , 2)) WHEN 'cash' THEN IF(b.booking_status = 'cancelled', 0, - FORMAT(IF(b.pickup_eta_dts > '2015-06-15 00:00:00',
             (b.gross_amount - (b.gross_amount - b.trip_charges * 0.12) + 1),
             (b.gross_amount - (b.gross_amount - b.trip_charges * 0.1) + 1)
            )
            , 2)) END AS book_owe,
             0 AS adjust_owe
            FROM `" . self::$table . "` b
            WHERE
            (IF (b.booking_completed_dts IS NULL,
             b.pickup_eta_dts BETWEEN :from_date AND :to_date,
             b.booking_completed_dts BETWEEN :from_date AND :to_date) AND CASE `b`.`payment_type` WHEN 'card' THEN
            b.driver_notification_status IN ('pob_confirmed_4', 'pob_confirmed_3', 'driver_not_show_3', 'driver_not_show_4') AND b.is_dispute <> 1 AND b.booking_status <> 'cancelled' WHEN 'cash' THEN
            b.driver_notification_status IN ('pob_confirmed_4', 'ask_live', 'live', 'pob_confirmed_3', 'driver_not_show_3', 'driver_not_show_4') AND b.booking_status <> 'cancelled' AND b.is_dispute <> 1 END AND TIMESTAMPDIFF(MINUTE, `b`.`pickup_eta_dts`, NOW()) > 5 AND (b.Status LIKE 'AUTHOR%' OR b.Status IS NULL OR b.Status LIKE '%successfully authorised%')
            AND b.mco_id = :mco_id AND (b.booking_completed_dts BETWEEN :from_date AND :to_date OR b.booking_completed_dts IS NULL)) UNION ALL
            SELECT a.mco_id,
             a.booking_linktext,
             0 AS booking_owe, SUM(a.amount) AS adjust_owe
            FROM booking_price_adjustments a, `" . self::$table . "` b
            WHERE
            a.mco_id = :mco_id AND a.timestamp BETWEEN :from_date AND :to_date AND a.deleted <> 1 AND CASE `b`.`payment_type` WHEN 'card' THEN
            b.driver_notification_status IN ('pob_confirmed_4', 'pob_confirmed_3', 'driver_not_show_3', 'driver_not_show_4') AND b.is_dispute <> 1 AND b.booking_status <> 'cancelled' WHEN 'cash' THEN
            b.driver_notification_status IN ('pob_confirmed_4', 'ask_live', 'live', 'pob_confirmed_3', 'driver_not_show_3', 'driver_not_show_4') AND b.booking_status <> 'cancelled' AND b.is_dispute <> 1 END AND IF (b.booking_completed_dts IS NULL,
             b.pickup_eta_dts BETWEEN :from_date AND :to_date,
             b.booking_completed_dts BETWEEN :from_date AND :to_date) AND b.`mco_id` = a.mco_id AND b.`booking_linktext` = a.booking_linktext
            GROUP BY a.booking_linktext) a_b_owe,
             `" . self::$table . "` AS b,
             address AS p_a,
             address AS d_a,
             customer AS c
            WHERE b.booking_linktext = a_b_owe.booking_linktext AND p_a.address_id = b.pickup_address_id AND d_a.address_id = b.destination_address_id AND c.customer_id = b.customer_id
            GROUP BY booking_linktext
        ";

        $rows = $this->findRows($sql, array('mco_id' => $mco_id, 'from_date' => $from_date, 'to_date' => $to_date));
        return $this->_toCollection($rows, EntityEmpty::getClassName());
    }

    /**
     * @param int $mco_id
     * @param string $from_date
     * @param string $to_date
     * @return array Entity colllection
     */
    public function getMcoPendings($mco_id, $from_date, $to_date)
    {
        $fields = self::$table . '.*, p.formatted_address AS pickup_address, p.formatted_address AS dropoff_address, c.title, c.firstname, c.lastname';
        $join = '
            LEFT JOIN `customer`AS c ON c.`customer_id` = ' . self::$table . '.`customer_id`
            LEFT JOIN `address` AS p ON p.`address_id` = ' . self::$table . '.pickup_address_id
            LEFT JOIN `address` AS d ON d.`address_id` = ' . self::$table . '.destination_address_id
        ';
        $where = "
            CASE `payment_type` WHEN 'card' THEN
                driver_notification_status NOT IN ('pob_confirmed_4','pob_confirmed_3','driver_not_show_3','driver_not_show_4') OR is_dispute = 1 WHEN 'cash' THEN
                driver_notification_status NOT IN ('pob_confirmed_4','ask_live','live','pob_confirmed_3','driver_not_show_3','driver_not_show_4')
                OR booking_status = 'cancelled' OR is_dispute = 1 END
            AND `pickup_eta_dts` BETWEEN :from_date AND :to_date
            AND `mco_id` = :mco_id
        ";
        $order = 'ORDER BY pickup_eta_dts ASC';
        $sql = 'SELECT ' . $fields . ' FROM `' . self::$table . "` $join " . ' WHERE ' . $where . ' ' . $order;
        $rows = $this->findRows($sql, array('mco_id' => $mco_id, 'from_date' => $from_date, 'to_date' => $to_date));
        return $this->_toCollection($rows, EntityEmpty::getClassName());
    }

    /**
     * @param int $mco_id
     * @param string $from_date
     * @param string $to_date
     * @return array
     */
    public function getAdjustmentsByDates($mco_id, $from_date, $to_date)
    {
        $sql = "
            SELECT a.* FROM booking_price_adjustments a, booking b
            WHERE a.mco_id = :mco_id
            AND a.booking_linktext = b.booking_linktext
            AND a.deleted <> 1
            AND IF(b.booking_status <> 'cancelled',
                    CASE
                        WHEN b.booking_completed_dts > :to_date
                            THEN a.timestamp  > '2094-08-24 23:59:59'
                        WHEN b.booking_completed_dts BETWEEN :from_date AND :to_date
                            THEN a.timestamp  < :to_date
                        WHEN b.booking_completed_dts < :from_date
                            THEN a.timestamp  BETWEEN :from_date AND :to_date
                    END,
                    CASE
                        WHEN b.cancellation_date > :from_date
                            THEN a.timestamp  > '2094-08-24 23:59:59'
                        WHEN b.cancellation_date BETWEEN :from_date AND :to_date
                            THEN a.timestamp  < :to_date
                        WHEN b.cancellation_date < :from_date
                            THEN a.timestamp  BETWEEN :from_date AND :to_date
                    END)
        ";
        $rows = $this->findRows($sql, array('mco_id' => $mco_id, 'from_date' => $from_date, 'to_date' => $to_date));

        $records = $this->_toCollection($rows, EntityEmpty::getClassName());
        $adjustments = array();
        foreach ($records as $rec) {
            if (!isset($adjustments[$rec->booking_linktext])) {
                $adjustments[$rec->booking_linktext] = array();
            }
            $adjustments[$rec->booking_linktext][] = $rec;
        }
        return $adjustments;
    }

}
