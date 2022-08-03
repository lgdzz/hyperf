<?php

declare (strict_types=1);

namespace lgdz\hyperf\model;

use Hyperf\DbConnection\Model\Model;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

/**
 * @property int $id
 * @property int $org_id
 * @property int $user_id
 * @property int $role_id
 * @property int $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @property-read Organization $org
 * @property-read Role $role
 * @property-read User $user
 * @property $extends
 */
class Account extends Model implements CacheableInterface
{
    use SoftDeletes;
    use Cacheable;
    use ExtendTrait;

    const status_ok = 1;
    const status_lock = 2;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'account';
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
    protected $casts = ['id' => 'integer', 'org_id' => 'integer', 'user_id' => 'integer', 'role_id' => 'integer', 'status' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    // 关联用户
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')
            ->select('id', 'username', 'phone', 'realname', 'from_channel', 'from_id', 'remark');
    }

    // 关联组织
    public function org()
    {
        return $this->belongsTo(Organization::class, 'org_id', 'id')
            ->select('id', 'path', 'path_name', 'name', 'name_en', 'full_name', 'grade_id', 'description', 'extends');
    }

    // 关联角色
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id')
            ->select('id', 'name', 'extends');
    }
}