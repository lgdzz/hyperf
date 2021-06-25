<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use lgdz\hyperf\model\File;
use lgdz\hyperf\Tools;
use lgdz\object\Query;

class FileService
{
    public function index(array $input)
    {
        $query = new Query($input);
        return Tools::P(
            File::query()->orderByDesc('id')->paginate($query->size)
        );
    }

    public function create(array $input)
    {
        $file = new File();
        $file->setFormData($input);
        $file->save();
    }

    public function update(int $id, array $input, ...$args)
    {
        $file = File::query()->where('id', $id)->firstOrFail();
        $file->setFormData($input);
        $file->save();
    }

    public function delete(int $id, ...$args)
    {
        File::destroy($id);
    }

    public function findById(int $id)
    {
        return File::query()->where('id', $id)->first();
    }

    /**
     * 验证参数是否是File对象，如不是抛出异常
     * @param $file
     * @return File
     */
    public function file($file): File
    {
        return ($file instanceof File) ? $file : Tools::E('文件不存在');
    }
}