<?php

declare (strict_types=1);

namespace lgdz\hyperf\model;

trait ExtendTrait
{
    public function setExtendsAttribute($value)
    {
        $this->attributes['extends'] = json_encode($value ?: [], JSON_UNESCAPED_UNICODE);
    }

    public function getExtendsAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }
}