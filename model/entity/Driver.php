<?php
namespace base\model\entity;

class Driver extends EntityBase
{

    public static $primaryKey = 'driver_id';
    protected static $_modelName = 'base\model\Driver';

    public $driver_id;
    public $mco_id;
    public $title;
    public $firstname;
    public $lastname;
    public $gender;
    public $registration_plate;
    public $contact_tel;
    public $driving_licence_number;
    public $cab_make;
    public $cab_model;
    public $pass_cap;
    public $status;
}