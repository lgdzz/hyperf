<?php

declare(strict_types=1);

namespace lgdz\hyperf\validator;

class UserValidator implements ValidatorInterface
{
    public function rule(): array
    {
        return [];
    }

    public function message(): array
    {
        return [];
    }

    public function custom(array $data, string $scene): void
    {
    }
}
