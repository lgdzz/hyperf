<?php

declare(strict_types=1);

namespace lgdz\hyperf\validator;

use lgdz\hyperf\Tools;

class DictionaryValidator implements ValidatorInterface
{
    public function rule(): array
    {
        return [
            'description' => 'required',
            'pid'         => 'required|integer|gte:0',
            'name'        => 'required_if:pid,0'
        ];
    }

    public function message(): array
    {
        return [
            'description.required' => '字典名称[description]未定义',
            'pid.required'         => 'Parent级[pid]未定义',
            'pid.integer'          => 'Parent级[pid]必须是整数型',
            'pid.gte'              => 'Parent级[pid]必须>=0',
            'name.required_if'     => '字典索引[name]在pid=0时必传'
        ];
    }

    public function custom(array $data, string $scene): void
    {
        
    }
}
