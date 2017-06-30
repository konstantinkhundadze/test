<?php

namespace base\helper;

use base\helper\Registry;
use base\model\DBConnection;

class PostcodeLocal {

    private static $_columns = array(
        'Department_Name' => array(
            'searcable' => true,
            'trim' => true,
        ),
        'Organisation_Name' => array(
            'searcable' => true,
            'trim' => true,
        ),
        'Number_Street' => array(
            'searcable' => true,
        ),
        'Street_Town' => array(
            'searcable' => true,
        ),
        'BuildingName_Street' => array(
            'searcable' => true,
        ),
        'Street_BuildingName' => array(
            'searcable' => true,
        ),
        'Street_Number' => array(
            'searcable' => true,
        ),
        'BuildingName_Town' => array(
            'searcable' => true,
        ),
        'Sub_Building_Name' => array(
            'searcable' => true,
            'trim' => true,
        ),
        'Building_Number' => array(
            'searcable' => true,
            'trim' => true,
        ),
        'Building_Name' => array(
            'searcable' => true,
            'trim' => true,
        ),
        'Thoroughfare' => array(
            'searcable' => true,
            'trim' => true,
        ),
        'Dependent_Thoroughfare' => array(
            'searcable' => true,
            'trim' => true,
        ),
        'Post_Town' => array(
            'searcable' => true,
            'trim' => true,
        ),
        'Postcode' => array(
            'searcable' => true,
        ),
        'Postcode2' => array(
            'searcable' => true,
        ),
        'Dependent_Locality' => array(
            'searcable' => false,
            'trim' => true,
        ),
    );

    public static function solrSearch($query)
    {
        if (!$query = urlencode(trim($query))) {
            return array();
        }

        $config = Registry::getConfig()->solr;

        $response = json_decode(file_get_contents($config->url.'solr/select/?q='.$query.'*&start='.$config->start.'&rows='.$config->rows.'&wt='.$config->wt), true);

        if ($response['response']['numFound'] == 0) {
            //fix
            $response = json_decode(file_get_contents($config->url.'solr/select/?q='.$query.'&start='.$config->start.'&rows='.$config->rows.'&wt='.$config->wt), true);
            if ($response['response']['numFound'] == 0) {
                return array();
            }
        }

        $data = $response['response']['docs'];

        foreach ($data as &$record) {
            $tmpRecord = array(
                'PC' => $record['Postcode'],
                'PCAID' => $record['AddressID'],
                'SC' => 'A'
            );

            foreach (self::$_columns as $column => $cOptions) {
                if (!empty($cOptions['trim']) && trim($record[$column])) {
                    $tmpRecord['L1'][] = $record[$column];
                }
            }

            $pc = explode(' ', $record['Postcode']);
            $tmpRecord['L1'][] = $pc[0];
            $tmpRecord['L1'] = implode(', ', $tmpRecord['L1']);
            $tmpRecord['FA'] = $tmpRecord['L1'];
            $record = $tmpRecord;
        }

        return $data;
    }

    public static function q($q)
    {
        $db = Registry::getApp()->getConnection(DBConnection::PAF_DB);

        $ts = $rows = array();
        foreach (explode(',', $q) as $term) {
            $cs = array();
            foreach (self::$_columns as $column => $cOptions) {
                if (!empty($cOptions['searcable'])) {
                    $cs[] = $column . ' LIKE "' . trim($term) . '%"';
                }
            }
            $ts[] = '(' . implode("\n OR ", $cs) . ')';
        }
        $w = implode("\n AND ", $ts);
        $q = 'SELECT * FROM address_columns WHERE ' . "\n" . $w . ' LIMIT 100';

        $res = $db->fetchRows($q);

        foreach ($res as $row) {
            $_fa = array();
            $fa = array();
            $_fa2 = array();
            $pc = explode(' ', $row['Postcode']);

            if (trim($row['Department_Name'])) {
                $fa[] = $row['Department_Name'];
            }
            if (trim($row['Organisation_Name'])) {
                $fa[] = $row['Organisation_Name'];
            }
            if (trim($row['Sub_Building_Name'])) {
                $fa[] = $row['Sub_Building_Name'];
            }
            if (trim($row['Building_Number'])) {
                $fa[] = $row['Building_Number'];
            }
            if (trim($row['Building_Name'])) {
                $fa[] = $row['Building_Name'];
            }
            if (trim($row['Thoroughfare'])) {
                $fa[] = $row['Thoroughfare'];
            }
            if (trim($row['Dependent_Thoroughfare'])) {
                $fa[] = $row['Dependent_Thoroughfare'];
            }
            if (trim($row['Dependent_Locality'])) {
                $fa[] = $row['Dependent_Locality'];
            }
            if (trim($row['Post_Town'])) {
                $fa[] = $row['Post_Town'];
            }
            $_fa = implode(', ', $fa);
            $_fa = $_fa . ', ' . $pc[0];
            $_fa2 = $_fa;

            $rows[] = array(
                'L1' => str_replace(', ,', ', ', $_fa),
                'FA' => $_fa2,
                'PC' => $row['Postcode'],
                'SC' => 'A',
                'PCAID' => $row['AddressID']
            );
        }

        return $rows;
    }

    public static function q_id($id)
    {
        $db = Registry::getApp()->getConnection(DBConnection::PAF_DB);

        $q = 'SELECT * FROM address_columns WHERE `AddressID` = ' . (int) $id . ' LIMIT 1';
        $res = $db->fetchRows($q);

        $rows = array();
        foreach ($res as $row) {
            $pc = explode(' ', $row['Postcode']);

            if (trim($row['Department_Name'])) {
                $fa[] = $row['Department_Name'];
            }
            if (trim($row['Organisation_Name'])) {
                $fa[] = $row['Organisation_Name'];
            }
            if (trim($row['Sub_Building_Name'])) {
                $fa[] = $row['Sub_Building_Name'];
            }
            if (trim($row['Building_Number'])) {
                $fa[] = $row['Building_Number'];
            }
            if (trim($row['Building_Name'])) {
                $fa[] = $row['Building_Name'];
            }
            if (trim($row['Thoroughfare'])) {
                $fa[] = $row['Thoroughfare'];
            }
            if (trim($row['Dependent_Thoroughfare'])) {
                $fa[] = $row['Dependent_Thoroughfare'];
            }
            if (trim($row['Dependent_Locality'])) {
                $fa[] = $row['Dependent_Locality'];
            }
            if (trim($row['Post_Town'])) {
                $fa[] = $row['Post_Town'];
            }
            $_fa = implode(', ', $fa);
            $_fa2 = $_fa . ', ' . $pc[0] . ' ' . $pc[1];

            $rows[] = array(
                'L1' => str_replace(', ,', ', ', $_fa),
                'FA' => $_fa2,
                'PC' => $row['Postcode'],
                'SC' => 'A',
                'PCAID' => $row['AddressID']
            );
            unset($_fa);
            unset($_fa2);
            unset($_fa);
            unset($fa);
        }

        return $rows;
    }

    public static function q_id_postcode_generate($id)
    {
        $db = Registry::getApp()->getConnection(DBConnection::PAF_DB);
        $q = 'SELECT * FROM address_columns WHERE `AddressID` = ?';
        $row = $db->fetchOne($q, $id);

        $fa = array();
        $out = array();
        $pc = explode(' ', $row['Postcode']);

        if (trim($row['Department_Name'])) {
            $fa[] = $row['Department_Name'];
        }
        if (trim($row['Organisation_Name'])) {
            $fa[] = $row['Organisation_Name'];
        }
        if (trim($row['Sub_Building_Name'])) {
            $fa[] = $row['Sub_Building_Name'];
        }
        if (trim($row['Building_Number'])) {
            $fa[] = $row['Building_Number'];
        }
        if (trim($row['Building_Name'])) {
            $fa[] = $row['Building_Name'];
        }
        if (trim($row['Thoroughfare'])) {
            $fa[] = $row['Thoroughfare'];
        }
        if (trim($row['Dependent_Thoroughfare'])) {
            $fa[] = $row['Dependent_Thoroughfare'];
        }
        if (trim($row['Dependent_Locality'])) {
            $fa[] = $row['Dependent_Locality'];
        }
        $_fa = implode(', ', $fa);
        $out['A'] = $_fa;

        if (trim($row['Post_Town'])) {
            $fa[] = $row['Post_Town'];
        }

        $_fa = implode(', ', $fa);
        $_fa2 = $_fa . ', ' . $pc[0] . ' ' . $pc[1];

        $out['L1'] = $_fa;
        $out['FA'] = $_fa2;
        unset($_fa);
        unset($_fa2);

        $out['T'] = $row['Post_Town'];
        $out['PC'] = $row['Postcode'];
        $out['L1'] = str_replace(', ,', ', ', $out['L1']);
        $out['SC'] = "A";
        $out['PCAID'] = $row['AddressID'];

        return $out;
    }

}
