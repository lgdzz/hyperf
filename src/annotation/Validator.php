<?php

namespace lgdz\hyperf\annotation;

use Hyperf\Di\Annotation\AbstractAnnotation;

/**
 * @Annotation
 * @Target("METHOD")
 */
class Validator extends AbstractAnnotation
{
    public $validator = '';

    public function __construct($value = null)
    {
        parent::__construct($value);
        $this->bindMainProperty('validator', $value);
    }
}