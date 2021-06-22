<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use App\Exception\BusinessException;
use App\Model\Dictionary;
use App\Utils\Tools;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;

class DictionaryService
{
    /**
     * @Inject()
     * @var Redis
     */
    private $Redis;

    public function index(array $input)
    {
        $name = $input['name'] ?? false;
        $id   = $input['id'] ?? false;
        $list = Dictionary::query()->when($name, function ($query, $value) {
            return $query->where('name', $value);
        })->when($id, function ($query, $value) {
            return $query->whereRaw("find_in_set({$value},path)");
        })->orderByRaw('sort asc,id asc')->get()->toArray();
        return Tools::$factory->Tree()->tree($list, 0);
    }

    public function read(int $id): Dictionary
    {
        return Dictionary::query()->where('id', $id)->firstOrFail();
    }

    public function create(array $input)
    {
        $pid  = $input['pid'] ?? 0;
        $name = $input['name'] ?? '';
        $path = '0';
        if ($pid > 0) {
            $parent = Dictionary::query()->where('id', $pid)->first();
            if (!($parent instanceof Dictionary)) {
                throw new BusinessException('parent级不存在');
            }
            $path = $parent->path;
        }
        $model              = new Dictionary();
        $model->pid         = $pid;
        $model->name        = $name;
        $model->description = $input['description'];
        $model->value       = $input['value'] !== '' ? $input['value'] : $input['description'];
        $model->sort        = $input['sort'] ?? 255;
        $model->save();
        // 保存path
        $model->path = $path . ',' . $model->id;
        if (!$model->name && isset($parent)) {
            $model->name = $parent->name . '_' . $model->id;
        }
        $model->save();
        $this->setCache();
    }

    public function update(int $id, array $input)
    {
        $model = $this->read($id);
        if (isset($input['name'])) {
            $model->name = $input['name'];
        }
        $model->description = $input['description'];
        $model->value       = $input['value'] !== '' ? $input['value'] : $input['description'];
        $model->sort        = $input['sort'] ?? 255;
        $model->save();
        $this->setCache();
    }

    public function delete(int $id)
    {
        if (Dictionary::query()->where('pid', $id)->count()) {
            throw new BusinessException('存在子级字典，无法直接删除');
        }
        Dictionary::query()->where('id', $id)->delete();
        $this->setCache();
    }

    public function get(string $name)
    {
        $root_id = Dictionary::query()->where('name', $name)->value('id');
        $tree    = $this->index(['id' => $root_id]);
        return $tree[0]['children'];
    }

    protected function setCache()
    {
        go(function () {
            $list   = Dictionary::query()->get();
            $values = [];
            foreach ($list as $row) {
                if ($row->description !== $row->value) {
                    preg_match('/(\w+)_\w+/', $row->name, $result);
                    if (isset($result[1])) {
                        $values[$result[1] . $row->value] = $row->description;
                    }
                }
            }
            $this->Redis->hMSet('dictionary', $values);
            Tools::$dictionary = $values;
        });
    }

    public function getEnum(string $name)
    {
        return array_column(
            array_map(function (Dictionary $row) {
                return [
                    'name'  => $row->description,
                    'value' => $row->value
                ];
            }, Dictionary::query()->where('pid', Dictionary::query()->where('name', $name)->value('id'))->get()->all()),
            'name',
            'value'
        );
    }
}