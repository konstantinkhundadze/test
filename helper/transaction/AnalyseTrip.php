<?php

namespace base\helper\transaction;

use base\model\entity\decorator\Transactions;

class AnalyseTrip {

    public static function flag(Transactions $trip)
    {
        $dodge_codes = array(
            'WD1',
            'WD2',
            'WD3',
            'WD4',
            'WD5',
            'WD6',
            'WD7',
            'WD17',
            'WD18',
            'WD19',
            'WD23',
            'WD24',
            'WD25',
            'WD99',
            'RM1',
            'RM2',
            'RM3',
            'RM4',
            'RM5',
            'RM6',
            'RM7',
            'RM8',
            'RM9',
            'RM10',
            'RM11',
            'RM12',
            'RM13',
            'RM14',
            'RM15',
            'RM16',
            'RM17',
            'RM18',
            'RM19',
            'RM20',
            'RM50',
            'HA0',
            'HA1',
            'HA2',
            'HA3',
            'HA4',
            'HA5',
            'HA6',
            'HA7',
            'HA8',
            'HA9',
            'UB1',
            'UB2',
            'UB3',
            'UB4',
            'UB5',
            'UB6',
            'UB7',
            'UB8',
            'UB9',
            'UB10',
            'UB11',
            'UB18',
            'IG1',
            'IG2',
            'IG3',
            'IG4',
            'IG5',
            'IG6',
            'IG7',
            'IG8',
            'IG9',
            'IG10',
            'IG11',
            'EN1',
            'EN2',
            'EN3',
            'EN4',
            'EN5',
            'EN6',
            'EN7',
            'EN8',
            'EN9',
            'EN10',
            'EN11',
            'CR0',
            'CR2',
            'CR3',
            'CR4',
            'CR5',
            'CR6',
            'CR7',
            'CR8',
            'CR9',
            'CR44',
            'CR90',
            'TW1',
            'TW2',
            'TW3',
            'TW4',
            'TW5',
            'TW7',
            'TW8',
            'TW9',
            'TW10',
            'TW11',
            'TW12',
            'TW13',
            'TW14',
            'TW15',
            'TW16',
            'TW17',
            'TW18',
            'TW19',
            'TW20',
            'DA1',
            'DA2',
            'DA3',
            'DA4',
            'DA5',
            'DA6',
            'DA7',
            'DA8',
            'DA9',
            'DA10',
            'DA11',
            'DA12',
            'DA13',
            'DA14',
            'DA15',
            'DA16',
            'DA17',
            'DA18',
            'SE23',
            'SE26',
            'SE20',
            'SE25',
            'SE15',
            'SE7',
            'SE18',
            'B8',
            'B11',
            'B13',
            'B15',
            'B16',
            'B19',
            'B20',
            'B21',
            'B37',
            'B64',
            'B67',
            'B70',
            'N2',
            'N3',
            'N4',
            'N5',
            'N6',
            'N7',
            'N8',
            'N9',
            'N10',
            'N11',
            'N12',
            'N13',
            'N14',
            'N15',
            'N16',
            'N17',
            'N18',
            'N19',
            'N20',
            'N21',
            'EH22',
            'G41',
            'L13',
            'L21',
            'L32',
            'L33',
            'L34',
            'M4',
            'M7',
            'M9',
            'M11',
            'M12',
            'M14',
            'M16',
            'M19',
            'M29',
            'M32',
            'M34',
            'M35',
            'E2',
            'E15',
            'E7',
            'E12',
            'E6',
            'E19',
            'E17',
            'E4',
            'SW4',
            'SW16',
            'KT8',
            'W2'
        );

        $except_postcodes = array(
            'W2 6BD',
            'W2 1HQ',
            'W2 1EE',
            'W2 1RH'
        );

        $flag_dodge = false;
        if ($trip->type == "RETURN") {
            $flag_dodge = true;
        } elseif ($trip->payment_type == 'card') {
            $date1 = new \DateTime($trip->pickup_eta_dts);
            $date2 = new \DateTime($trip->booking_date);
            $diff = $date2->diff($date1);

            if ($diff->h <= 1 && $diff->d == 0) {
                $min = $diff->h * 60 + $diff->i;
                if ($min <= 60) {
                    $flag_dodge = true;
                }
            }

            if ($trip->gross_amount > 200) {
                $flag_dodge = true;
            }
            if ($trip->gross_amount > 100) {
                if ($diff->h <= 1 && $diff->d == 0) {
                    $min = $diff->h * 60 + $diff->i;
                    if ($min <= 90) {
                        $flag_dodge = true;
                    }
                }
            }

            $pickup_postcode = explode(" ", $trip->getPickupAddress()->postcode);
            $pickup_postcode = $pickup_postcode[0];

            $drop_postcode = explode(" ", $trip->getDestinationAddress()->postcode);
            $drop_postcode = $drop_postcode[0];

            $pickup = !in_array($trip->getPickupAddress()->postcode, $except_postcodes);
            $dropoff = !in_array($trip->getDestinationAddress()->postcode, $except_postcodes);

            foreach ($dodge_codes as $item) {
                if (($pickup_postcode == $item && $pickup) || ($drop_postcode == $item && $dropoff)) {
                    $flag_dodge = true;
                    break;
                }
            }

            if (preg_match('/(outlook|msn|aol)/', $trip->getCustomer()->customer_email)) {
                $flag_dodge = true;
            }
        }
        return $flag_dodge;
    }

}
