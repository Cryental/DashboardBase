<?php

namespace App\DataTransferObjects;

use ReflectionClass;
use ReflectionProperty;
use stdClass;

abstract class DataTransferObjectBase
{
    protected $entity;

    public function __construct($entity)
    {
        $this->entity = $entity;
    }
}
