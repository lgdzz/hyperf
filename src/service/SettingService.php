<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use lgdz\hyperf\model\Setting;

class SettingService
{
    public function set(string $name, $value): void
    {
        $model = Setting::query()->where('name', $name)->first();
        if (!($model instanceof Setting)) {
            $model = new Setting();
            $model->name = $name;
        }
        $model->value = $value;
        $model->save();
    }

    public function get(string $name)
    {
        $model = Setting::query()->where('name', $name)->first();
        return ($model instanceof Setting) ? $model->value : null;
    }
}