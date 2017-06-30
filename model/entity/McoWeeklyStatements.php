<?php

namespace base\model\entity;

class McoWeeklyStatements extends EntityBase {

    const STATUS_PAID = 'paid';
    const STATUS_NOT_PAID = 'not_paid';

    public static $primaryKey = 'id';
    protected static $_auxiliaryFields = array('previous_owe');

    /** @var int */
    public $id;

    /** @var int */
    public $mco_id;

    /** @var DateTime */
    public $from_date;

    /** @var DateTime */
    public $to_date;

    /** @var int */
    public $cash_jobs_count;

    /** @var int */
    public $card_jobs_count;

    /** @var float */
    public $total;

    /** @var enum */
    public $status = self::STATUS_NOT_PAID;

    /** @var float */
    public $vat;

    /** @var DateTime */
    public $date_paid;

    /** @var float */
    public $previous_owe;

}
