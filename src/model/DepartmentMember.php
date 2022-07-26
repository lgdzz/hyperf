<?php

declare (strict_types=1);

namespace lgdz\hyperf\model;

use Hyperf\DbConnection\Model\Model;

/**
 * @property int $department_id
 * @property int $account_id
 * @property int $org_id
 * @property int $is_leader
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class DepartmentMember extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'department_member';
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
    protected $casts = ['department_id' => 'integer', 'account_id' => 'integer', 'org_id' => 'integer', 'is_leader' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}