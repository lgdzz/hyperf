<?php

declare (strict_types=1);
namespace lgdz\hyperf\model;

use Hyperf\DbConnection\Model\Model;
/**
 * @property int $id 
 * @property string $type 
 * @property int $role_id 
 * @property string $phone 
 * @property string $username 
 * @property string $password 
 * @property string $salt 
 * @property string $job_number 
 * @property int $status 
 * @property int $is_system 
 * @property string $remark 
 * @property string $last_ip 
 * @property int $last_time 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class User extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user';
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
    protected $casts = ['id' => 'integer', 'role_id' => 'integer', 'status' => 'integer', 'is_system' => 'integer', 'last_time' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}