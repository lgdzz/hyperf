<?php

declare (strict_types=1);

namespace lgdz\hyperf\model;

use Hyperf\DbConnection\Model\Model;

/**
 * @property int $id
 * @property int $pid
 * @property string $path
 * @property string $name
 * @property int $master
 * @property int $is_disable
 * @property int $is_system
 * @property string $remark
 * @property string $rules
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Role extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'role';
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
    protected $casts = ['id' => 'integer', 'pid' => 'integer', 'master' => 'integer', 'is_disable' => 'integer', 'is_system' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}