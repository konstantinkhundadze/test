<?php

namespace base\model\entity\decorator;

use base\model\ModelPDO;
use base\model\MySqlQueryBuilder;
use base\model\Mco as McoModel;
use base\model\Address as AddressModel;
use base\model\entity\EntityEmpty;

class Mco extends DecoratorAbstract {

    /**
     * @var string
     */
    private $_details;

    /**
     * @todo
     * @return \base\model\entity\EntityEmpty
     */
    public function getDetails()
    {
        if (is_null($this->_details)) {
            $model = new ModelPDO();
            $query = $model
                    ->select(McoModel::$table)
                    ->columns(array(
                        'address.*',
                        'p.hours',
                        'p.capacity',
                        'p.start',
                        'p.finish',
                        'pause_time' => "DATE_FORMAT(registration_dt, '%d-%b-%Y')  as registration_dt, TIMEDIFF(p.finish, NOW())",
                        'airport' => 'am.id'
                    ))
                    ->join(array('am' => 'affiliate_mco'), 'am.mco_id = mco.mco_id', MySqlQueryBuilder::SQL_LEFT)
                    ->join(array('p' => 'mco_pause'), 'p.id = mco.pause_id', MySqlQueryBuilder::SQL_LEFT)
                    ->join(AddressModel::$table, 'mco.registered_address_id = address.address_id', MySqlQueryBuilder::SQL_LEFT)
                    ->where('mco.mco_id')
                    ->where('(affilate_id = 35 OR affilate_id IS NULL)', '', '');
            $this->_details = new EntityEmpty($model->findOne($query, array($this->mco_id)));
        }
        return $this->_details;
    }

}
