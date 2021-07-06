<?php

declare (strict_types=1);

namespace lgdz\hyperf\model;

use Hyperf\Database\Model\Events\Creating;
use Hyperf\Database\Model\Events\Updating;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;
use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;
use lgdz\Factory;
use lgdz\hyperf\Tools;

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
 * @property-read Role $role
 */
class User extends Model implements CacheableInterface
{
    use SoftDeletes;
    use Cacheable;

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

    public function creating(Creating $event)
    {
        $this->salt = Factory::container()->helper->randomString();
        $this->password = $this->encodePassword($this->password);
    }

    public function updating(Updating $event)
    {
        if ($this->password !== $this->original['password']) {
            $this->password = $this->encodePassword($this->password);
        }
    }

    // 关联用户角色
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    public function initRootUser(): void
    {
        $this->type = 'master';
        $this->username = 'root';
        $this->password = '123456';
        $this->remark = '系统管理员账号';
        $this->role_id = 1;
        $this->phone = '';
        $this->save();
    }

    // 验证密码
    public function checkPassword(string $password): bool
    {
        return Factory::container()->password->check($password, $this->salt, $this->password);
    }

    // 密码加密
    private function encodePassword(string $password): string
    {
        if (!$this->salt) {
            Tools::E('salt未创建');
        }
        return Factory::container()->password->build($password, $this->salt);
    }

    // 隐藏密码
    public function hiddenPassword()
    {
        $this->addHidden('password', 'salt');
        return $this;
    }
}