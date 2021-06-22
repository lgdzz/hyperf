<?php

declare (strict_types=1);

namespace lgdz\hyperf\model;

use Hyperf\DbConnection\Model\Model;

/**
 * @property int $id
 * @property int $c_id
 * @property string $channel
 * @property int $type
 * @property string $filename
 * @property string $filepath
 * @property int $filesize
 * @property string $mimetype
 * @property string $extension
 * @property string $extra
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class File extends Model
{
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
    protected $casts = ['id' => 'integer', 'c_id' => 'integer', 'type' => 'integer', 'filesize' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}