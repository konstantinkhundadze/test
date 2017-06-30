<?php

namespace base\model\entity;

class SearchAddressLog extends EntityBase {

    public static $primaryKey = 'id';

    protected static $_modelName = 'base\model\SearchAddressLog';

    /** @var int */
    public $id;

    /** @var string */
    public $from_id;

    /** @var string */
    public $to_id;

    /** @var string */
    public $ip;

    /** @var string */
    public $session_id;

    /** @var string */
    public $user_agent;

    /** @var DateTime */
    public $timestamp;

    /** @var int */
    public $status;

    /** @var int */
    public $origination_source;

    /** @var int */
    public $affiliate_id;

    /** @var string */
    public $via_ids;
    
    /** @var int */
    public $search_log_id;

}
