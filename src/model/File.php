<?php

declare (strict_types=1);

namespace lgdz\hyperf\model;

use Hyperf\DbConnection\Model\Model;

/**
 * @property int $id
 * @property int $c_id
 * @property int $org_id
 * @property string $from_id
 * @property string $channel
 * @property string $type
 * @property string $filename
 * @property string $filepath
 * @property int $filesize
 * @property string $mimetype
 * @property string $extension
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property  $extra
 */
class File extends Model
{
    const TYPE_IMAGE = 'image';
    const TYPE_VIDEO = 'video';
    const TYPE_AUDIO = 'audio';
    const TYPE_FILE = 'file';
    const TYPE_WORD = 'word';
    const TYPE_EXCEL = 'excel';
    const TYPE_ZIP = 'zip';
    const TYPE_PDF = 'pdf';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'file';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'c_id' => 'integer', 'org_id' => 'integer', 'filesize' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    public function setExtraAttribute($value)
    {
        $this->attributes['extra'] = json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function getExtraAttribute($value)
    {
        return json_decode($value, true);
    }
}