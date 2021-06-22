<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use App\Model\Rule;
use App\Utils\Tools;

class RuleService extends AbstractResourceService
{
    public function index(array $input)
    {
        return Rule::query()->orderByRaw('sort asc,id asc')->get();
    }

    public function read(int $id, ...$args)
    {
        return Rule::query()->where('id', $id)->firstOrFail();
    }

    public function create(array $input)
    {
        $rule = new Rule();
        $rule->setFormData($input);
        $rule->save();
        // 生成path
        $path = Rule::query()->where('id', $rule->pid)->value('path');;
        $rule->path = $path . ',' . $rule->id;
        $rule->save();
    }

    public function update(int $id, array $input, ...$args)
    {
        $rule = Rule::query()->where('id', $id)->firstOrFail();
        $rule->setFormData($input);
        $rule->save();
        // 生成path
        $path = Rule::query()->where('id', $rule->pid)->value('path');;
        $rule->path = $path . ',' . $rule->id;
        $rule->save();
    }

    public function delete(int $id, ...$args)
    {
        Rule::destroy($id);
    }
}