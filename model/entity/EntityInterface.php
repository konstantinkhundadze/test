<?php

namespace base\model\entity;

interface EntityInterface {

    /**
     * Get primary key field value
     */
    public function getPK();

    public function load($data);

    public function toArray();

    public function validate();
}
