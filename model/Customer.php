<?php

namespace base\model;

class Customer extends ModelPDO {

    protected static $_entityClass = 'base\model\entity\Customer';
    public static $table = 'customer';

}
