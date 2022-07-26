<?php

declare(strict_types=1);

namespace lgdz\hyperf\service;

use Hyperf\Di\Annotation\Inject;
use League\Flysystem\Filesystem;
use lgdz\Factory;
use lgdz\hyperf\Tools;

class UploadService
{
    /**
     * @Inject()
     * @var Filesystem
     */
    protected $filesystem;

    public function backstage(int $type, string $from_id = '')
    {
        return $this->upload('system', $from_id, $type);
    }

    public function member(int $type, string $from_id)
    {
        return $this->upload('member', $from_id, $type);
    }

    /**
     * @param string $channel
     * @param string $from_id
     * @param int $type
     * @return array
     */
    public function upload(string $channel, string $from_id, int $type)
    {
        $request = Tools::I();
        $name = 'file';
        !$request->hasFile($name) && Tools::E('上传文件获取失败');
        $upload = $request->file($name);
        $remote_path = sprintf('%s/%s/%s/%s', $channel, $from_id, date('Y-m-d'), date('YmdHis') . Factory::container()->helper->randomNumber(4));
        $result = $this->filesystem->put($remote_path, $upload->getStream(), ['Content-Type' => $upload->getMimeType()]);
        if ($result === true) {
            return [
                'mimetype'  => $upload->getMimetype(),
                'filesize'  => $upload->getSize(),
                'extension' => $upload->getExtension(),
                'filename'  => $upload->getClientFilename(),
                'filepath'  => $remote_path,
                'from_id'   => $from_id,
                'channel'   => $channel,
                'type'      => $type
            ];
        } else {
            Tools::E('上传失败');
        }
    }
}