<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use lgdz\hyperf\model\Setting;
use lgdz\hyperf\Tools;

class SettingService
{
    // 保存配置到数据库并写入缓存
    public function set(int $org_id, string $name, $value, int $account_id = 0): void
    {
        $model = Setting::query()->where('org_id', $org_id)->where('account_id', $account_id)->where('name', $name)->first();
        if (!($model instanceof Setting)) {
            $model = new Setting();
            $model->org_id = $org_id;
            $model->account_id = $account_id;
            $model->name = $name;
        }
        $model->value = $value;
        $this->cacheSync($this->cacheKey($model->org_id, $model->name, $model->account_id), $value);
        $model->save();
    }

    // 从数据库中获取配置值
    public function get(int $org_id, string $name, int $account_id = 0)
    {
        $model = Setting::query()->where('org_id', $org_id)->where('account_id', $account_id)->where('name', $name)->first();
        return ($model instanceof Setting) ? $model->value : null;
    }

    // 获取缓存key
    private function cacheKey(int $org_id, string $name, int $account_id = 0)
    {
        return sprintf('setting:%d_%d_%s', $org_id, $account_id, $name);
    }

    // 配置值写入缓存
    private function cacheSync(string $key, $value)
    {
        Tools::C_set($key, $value);
    }

    // 从缓存中读取配置值，如果值不存在则从数据库中读取并写入缓存
    public function getByCache(int $org_id, string $name, int $account_id = 0)
    {
        $key = $this->cacheKey($org_id, $name, $account_id);
        $value = Tools::C_get($key);
        if (!$value) {
            $value = $this->get($org_id, $name, $account_id);
            $this->cacheSync($key, $value);
        }
        return $value;
    }
}