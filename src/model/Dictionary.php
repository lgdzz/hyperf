<?php

declare (strict_types=1);

namespace lgdz\hyperf\model;

use Hyperf\DbConnection\Model\Model;

/**
 * @property int $id
 * @property int $pid
 * @property string $name
 * @property string $description
 * @property string $value
 * @property string $value_type
 * @property string $path
 * @property int $sort
 */
class Dictionary extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dictionary';
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
    protected $casts = ['id' => 'integer', 'pid' => 'integer', 'sort' => 'integer'];

    public $timestamps = false;
}