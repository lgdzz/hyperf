<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use lgdz\Factory;
use lgdz\hyperf\model\Dictionary;
use lgdz\hyperf\Tools;
use lgdz\object\Body;

class DictionaryService
{
    public function index()
    {
        $list = Dictionary::query()->when($id, function ($query, $value) {
            return $query->whereRaw("find_in_set({$value},path)");
        })->orderByRaw('sort asc,id asc')->get()->toArray();
        return empty($list) ? [] : Factory::container()->tree->build($list, $list[0]['pid']);
    }

    public function findById(int $id)
    {
        return Dictionary::query()->where('id', $id)->first();
    }

    /**
     * 验证参数是否是Dictionary对象，如不是抛出异常
     * @param $dictionary
     * @return Dictionary
     */
    public function dictionary($dictionary): Role
    {
        return (dictionary instanceof Dictionary) ? $dictionary : Tools::E('字典不存在');
    }

    public function create(Body $input)
    {
        $pid = (int)$input->pid;
        if ($pid > 0) {
            $parent = Dictionary::query()->where('id', $pid)->first();
            !($parent instanceof Dictionary) && Tools::E('上级字典不存在');
            $path = $parent->path;
        } else {
            $path = '0';
        }
        $model = new Dictionary();
        $model->pid = $pid;
        $model->name = $input->name;
        $model->description = $input->description;
        $model->value = $input->value ?: $input->description;
        $model->sort = $input->sort ?: 255;
        $model->save();
        // 保存path
        $model->path = $path . ',' . $model->id;
        if (!$model->name && isset($parent)) {
            $model->name = $parent->name . '_' . $model->id;
        }
        $model->save();
    }

    public function update(int $id, Body $input)
    {
        $model = $this->dictionary($this->findById($id));
        if ($input->name) {
            $model->name = $input->name;
        }
        $model->description = $input->description;
        $model->value = $input->value ?: $input->description;
        $model->sort = $input->sort ?: 255;
        $model->save();
    }

    public function delete(int $id)
    {
        if (Dictionary::query()->where('pid', $id)->count()) {
            Tools::E('请删除子级字典后再删除');
        }
        $model = $this->dictionary($this->findById($id));
        $model->delete();
    }

//    public function get(string $name)
//    {
//        $root_id = Dictionary::query()->where('name', $name)->value('id');
//        $tree = $this->index(['id' => $root_id]);
//        return $tree[0]['children'];
//    }
//
//    protected function setCache()
//    {
//        go(function () {
//            $list = Dictionary::query()->get();
//            $values = [];
//            foreach ($list as $row) {
//                if ($row->description !== $row->value) {
//                    preg_match('/(\w+)_\w+/', $row->name, $result);
//                    if (isset($result[1])) {
//                        $values[$result[1] . $row->value] = $row->description;
//                    }
//                }
//            }
//            $this->Redis->hMSet('dictionary', $values);
//            Tools::$dictionary = $values;
//        });
//    }
}