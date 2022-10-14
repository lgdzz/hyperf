<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use lgdz\hyperf\model\File;
use lgdz\hyperf\model\FileCategory;
use lgdz\hyperf\Tools;
use lgdz\object\Body;
use lgdz\object\Query;

class FileCategoryService
{
    public function index(Query $input)
    {
        return FileCategory::query()->when($input->org_id, function ($query, $value) {
            return $query->where('org_id', $value);
        })->when($input->type, function ($query, $value) {
            return $query->where('type', $value);
        })->orderBy('id')->get();
    }

    public function create(Body $input)
    {
        $category = new FileCategory();
        $category->org_id = $input->org_id;
        $category->type = $input->type ?? '';
        $category->name = $input->name;
        $category->save();
    }

    public function update(int $id, Body $input)
    {
        $category = $this->category($this->findById($id));
        $category->name = $input->name;
        $category->save();
    }

    public function delete($id)
    {
        $category = $this->category($this->findById($id));
        $category->org_id !== Tools::Org()->id && Tools::E('无权限删除');
        if (File::query()->where('c_id', $category->id)->exists()) {
            Tools::E('分类下包含文件，无法删除');
        }
        $category->delete();
    }

    public function findById(int $id)
    {
        return FileCategory::query()->where('id', $id)->first();
    }

    /**
     * 验证参数是否是FileCategory对象，如不是抛出异常
     * @param $category
     * @return FileCategory
     */
    public function category($category): FileCategory
    {
        return ($category instanceof FileCategory) ? $category : Tools::E('文件分类不存在');
    }
}