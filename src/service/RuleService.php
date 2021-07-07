<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use lgdz\Factory;
use lgdz\hyperf\model\Rule;
use lgdz\hyperf\Tools;
use lgdz\object\Body;

class RuleService
{
    public function index()
    {
        $list = Rule::query()->orderByRaw('sort asc,id asc')->get()->toArray();
        return empty($list) ? [] : Factory::container()->tree->build($list, $list[0]['pid']);
    }

    public function create(Body $input): void
    {
        $rule = new Rule();
        $rule->setFormData($input);
        $rule->save();
        // 生成path
        $path = Rule::query()->where('id', $rule->pid)->value('path');;
        $rule->path = $path . ',' . $rule->id;
        $rule->save();
    }

    public function update(int $id, Body $input): void
    {
        $rule = $this->rule($this->findById($id));
        $rule->setFormData($input);
        $rule->save();
        // 生成path
        $path = Rule::query()->where('id', $rule->pid)->value('path');;
        $rule->path = $path . ',' . $rule->id;
        $rule->save();
    }

    public function delete(int $id)
    {
        $rule = $this->rule($this->findById($id));
        count($rule->children) > 0 && Tools::E('请先删除子规则');
        try {
            $rule->delete();
        } catch (\Exception $e) {
            Tools::E('删除失败');
        }
    }

    public function findById(int $id)
    {
        return Rule::query()->where('id', $id)->first();
    }

    /**
     * 验证参数是否是Rule对象，如不是抛出异常
     * @param $rule
     * @return Rule
     */
    public function rule($rule): Rule
    {
        return ($rule instanceof Rule) ? $rule : Tools::E('权限规则不存在');
    }
}