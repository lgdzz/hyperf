<?php

declare(strict_types=1);

namespace lgdz\hyperf\validator;

interface ValidatorInterface
{
    // hyperf表单验证规则
    public function rule(): array;

    // hyperf验证失败错误信息
    public function message(): array;

    // 自定义表单验证
    public function custom(array $data, string $scene): void;
}
