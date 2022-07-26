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
        $list = Dictionary::query()->orderByRaw('sort asc,id asc')->get()->toArray();
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
    public function dictionary($dictionary): Dictionary
    {
        return ($dictionary instanceof Dictionary) ? $dictionary : Tools::E('字典不存在');
    }

    private function is_numeric($value)
    {
        return is_numeric($value) && (strlen($value) === 1 || substr($value, 0, 1) > 0);
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
        $model->name = $input->name ?? '';
        $model->description = $input->description;
        $model->value = $input->value != '' ? $input->value : $input->description;
        $model->sort = $input->sort ?: 255;
        $model->save();
        // 二次处理保存
        $model->path = $path . ',' . $model->id;
        if ($input->value_type) {
            $model->value_type = $input->value_type;
        } else {
            $model->value_type = $this->is_numeric($model->value) ? 'int' : 'string';
        }
        $model->name = $model->name ?: ($parent->name . '_' . $model->id);
        $model->save();
        $this->buildCache();
    }

    public function update(int $id, Body $input)
    {
        $model = $this->dictionary($this->findById($id));
        if ($input->name) {
            $model->name = $input->name;
        }
        $model->description = $input->description;
        $model->value = $input->value != '' ? $input->value : $input->description;
        if ($input->value_type) {
            $model->value_type = $input->value_type;
        } else {
            $model->value_type = $this->is_numeric($model->value) ? 'int' : 'string';
        }
        $model->sort = $input->sort ?: 255;
        $model->save();
        $this->buildCache();
    }

    public function delete(int $id)
    {
        if (Dictionary::query()->where('pid', $id)->count()) {
            Tools::E('请删除子级字典后再删除');
        }
        $model = $this->dictionary($this->findById($id));
        $model->delete();
        $this->buildCache();
    }

    // 更新内存中的缓存字典
    public function buildCache()
    {
        $list = Dictionary::query()->selectRaw('id,pid,description label,name,value,value_type')->orderByRaw('sort asc,id asc')->get()->toArray();
        $id_index_list = [];
        foreach ($list as $item) {
            $id_index_list[str_replace('_' . $item['id'], '_' . $item['value'], $item['name'])] = $item['label'];
        }
        $name_index_tree = array_column(empty($list) ? [] : Tools::F()->tree->build(array_map(function ($row) {
            // 如果数据值为int则转换类型
            if ($row['value_type'] === 'int') {
                $row['value'] = (int)$row['value'];
            }
            return $row;
        }, $list), $list[0]['pid']), null, 'name');
        Tools::D2Cache($id_index_list, $name_index_tree);
    }
}