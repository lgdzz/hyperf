<?php

declare(strict_types=1);

namespace lgdz\hyperf\controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\Middlewares;
use Hyperf\HttpServer\Annotation\RequestMapping;
use League\Flysystem\Filesystem;
use lgdz\hyperf\middleware\AccountMiddleware;
use lgdz\hyperf\middleware\AuthUserMiddleware;
use lgdz\hyperf\model\File;
use lgdz\hyperf\service\FileService;
use lgdz\hyperf\Tools;
use lgdz\object\Body;

/**
 * @Controller()
 * @Middlewares({
 *     @Middleware(AuthUserMiddleware::class),
 *     @Middleware(AccountMiddleware::class)
 * })
 */
class UploadController
{
    /**
     * @Inject()
     * @var FileService
     */
    protected $FileService;

    /**
     * 上传图片
     * @RequestMapping(path="/l/upload/image", methods="post")
     */
    public function image(Filesystem $filesystem)
    {
        !Tools::I()->hasFile('file') && Tools::E('请选择上传的图片');
        $file = Tools::I()->file('file');
        $stream = fopen($file->getRealPath(), 'r+');
        $path = Tools::StoragePath('backstage/' . Tools::Org()->id, Tools::F()->helper->randomName() . '.' . $file->getExtension());
        $filesystem->writeStream($path, $stream);
        fclose($stream);
        // 存储图片信息
        $this->FileService->create(new Body([
            'channel'   => 'backstage',
            'org_id'    => Tools::Org()->id,
            'from_id'   => Tools::Account()->id,
            'from_id'   => Tools::Account()->id,
            'type'      => File::TYPE_IMAGE,
            'filename'  => $file->getClientFilename(),
            'filepath'  => $path,
            'filesize'  => $file->getSize(),
            'mimetype'  => $file->getMimeType(),
            'extension' => $file->getExtension()
        ]));
        return Tools::Ok([
            'name' => $file->getClientFilename(),
            'path' => $path
        ]);
    }
}