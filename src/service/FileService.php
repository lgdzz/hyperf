<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use App\Model\File;
use App\Utils\Tools;

class FileService extends AbstractResourceService
{
    public function index(array $input)
    {
        $page_size = $input['page_size'];
        return Tools::P(
            File::query()->orderByDesc('id')->paginate((int)$page_size)
        );
    }

    public function read(int $id, ...$args)
    {
        return File::query()->where('id', $id)->firstOrFail();
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
}