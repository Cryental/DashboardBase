<?php

namespace App\DataTransferObjects;

abstract class DataTransferObjectBase
{
    protected $entity;

    public function __construct($entity)
    {
        $this->entity = $entity;
    }
}
