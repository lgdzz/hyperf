<?php

declare (strict_types=1);

namespace lgdz\hyperf\model;

use Hyperf\DbConnection\Model\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $ip_isp
 * @property string $login_ip
 * @property string $login_time
 * @property string $channel
 */
class LoginLog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'login_log';
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
    protected $casts = ['id' => 'integer', 'user_id' => 'integer'];

    public $timestamps = false;
}