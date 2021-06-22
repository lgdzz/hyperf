<?php

declare (strict_types=1);

namespace lgdz\hyperf\model;

use Hyperf\DbConnection\Model\Model;

/**
 * @property int $id
 * @property int $pid
 * @property string $path
 * @property string $name
 * @property string $type
 * @property string $method
 * @property string $permission_id
 * @property string $operation
 * @property string $service_router
 * @property string $client_router
 * @property string $client_route_name
 * @property string $icon
 * @property int $sort
 */
class Rule extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rule';
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