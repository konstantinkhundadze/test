<?php

namespace base\model\entity;

class DriverNotificationAnswers extends EntityBase {

    public static $primaryKey = 'id';
    protected static $_modelName = 'base\model\DriverNotificationAnswers';
    
    protected static $_auxiliaryFields = array('status_question', 'status_answer', 'status_stage');

    /** @var int */
    public $id;

    public $comment;
    public $driver_answer;
    public $status_timestamp;
    public $mco_id;
    public $booking_linktext;
    public $author = null;

    /** @var string auxiliary field */
    public $status_question = '';

    /** @var string auxiliary field */
    public $status_answer = '';

    /** @var string auxiliary field */
    public $status_stage = '';

    protected function _afterLoad()
    {
        parent::_afterLoad();
        $this->_statusQuestionAnswer();
    }

    private function _statusQuestionAnswer()
    {
        $_modelName = $this->_getModel();
        if ($result = $_modelName::getNoticeWordingByDbAnswer($this->driver_answer)) {
            $this->status_question = $result['question'];
            $this->status_answer = $result['answer'];
            $this->status_stage = $result['stage'];
        }
    }
}
