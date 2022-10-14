<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use lgdz\hyperf\model\File;
use lgdz\hyperf\Tools;
use lgdz\object\Body;
use lgdz\object\Query;

class FileService
{
    public function index(Query $input)
    {
        return Tools::P(
            File::query()->when($input->org_id, function ($query, $value) {
                return $query->where('org_id', $value);
            })->when($input->channel, function ($query, $value) {
                return $query->where('channel', $value);
            })->when($input->from_id, function ($query, $value) {
                return $query->where('from_id', $value);
            })->when($input->type, function ($query, $value) {
                return $query->where('type', $value);
            })->when($input->c_id, function ($query, $value) {
                return $query->where('c_id', $value);
            })->orderByDesc('id')->paginate($input->size)
        );
    }

    public function create(Body $input): File
    {
        $file = new File();
        $file->c_id = $input->c_id ?? 0;
        $file->channel = $input->channel ?? 'backstage';
        $file->org_id = $input->org_id ?? Tools::Org()->id;
        $file->from_id = $input->from_id ?? Tools::Account()->id;
        $file->type = $input->type;
        $file->filename = $input->filename;
        $file->filepath = $input->filepath;
        $file->filesize = $input->filesize;
        $file->mimetype = $input->mimetype;
        $file->extension = $input->extension;
        $file->extra = $input->extra ?? [];
        $file->save();
        return $file;
    }

    public function delete($id)
    {
        $file = $this->file($this->findById($id));
        $file->org_id !== Tools::Org()->id && Tools::E('无权限删除');
        $file->delete();
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