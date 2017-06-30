<?php

namespace base\model;

class Driver extends ModelPDO
{
    const STATUS_ACTIVE = 'active';
    const STATUS_DELETED = 'deleted';

    protected static $_entityClass = 'base\model\entity\Driver';
    public static $table = 'driver';

    public function getMcoDrivers($mcoId, $status = '')
    {
        $params = array('mco_id' => $mcoId);
        if ($status) {
            $params['status'] = $status;
        }
        $sql = $this->select()->where(array_keys($params));
        $rows = $this->findRows($sql, $params);
        return $this->_toCollection($rows);
    }

}