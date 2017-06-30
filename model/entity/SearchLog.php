<?php

namespace base\model\entity;

class SearchLog extends EntityBase {

    public static $primaryKey = 'search_id';

    protected static $_modelName = 'base\model\SearchLog';

    /** @var int */
    public $search_id;

    /** @var string */
    public $session_id;

    /** @var string */
    public $from;

    /** @var string */
    public $to;

    /** @var DateTime */
    public $arrival_time;

    /** @var DateTime */
    public $departure_time;

    /** @var int */
    public $return_journey;

    /** @var DateTime */
    public $waiting_time;

    /** @var int */
    public $number_of_passengers;

    /** @var int */
    public $wheelchair;

    /** @var int */
    public $female_driver;

    /** @var int */
    public $extra_luggage;

    /** @var int */
    public $book_reminder_call;

    /** @var string */
    public $car_type;

    /** @var float */
    public $journey_distance_miles;

}
