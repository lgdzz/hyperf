<?php

declare (strict_types=1);

namespace lgdz\hyperf\model;

use Hyperf\Database\Model\Events\Creating;
use Hyperf\Database\Model\Events\Updating;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;
use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;
use lgdz\hyperf\Tools;

/**
 * @property int $id
 * @property int $site_id
 * @property string $type
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
 * @property-read Account[] $account
 * @property-read int $account_count
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
    protected $casts = ['id' => 'integer', 'site_id' => 'integer', 'status' => 'integer', 'is_system' => 'integer', 'last_time' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    public function creating(Creating $event)
    {
        $this->salt = Tools::F()->helper->randomString();
        $this->password = $this->encodePassword($this->password);
    }

    public function updating(Updating $event)
    {
        if ($this->password !== $this->original['password']) {
            $this->password = $this->encodePassword($this->password);
        }
    }

    // 关联账户
    public function account()
    {
        return $this->hasMany(Account::class, 'user_id', 'id');
    }

    // 验证密码
    public function checkPassword(string $password): bool
    {
        return Tools::F()->password->check($password, $this->salt, $this->password);
    }

    // 密码加密
    private function encodePassword(string $password): string
    {
        if (!$this->salt) {
            Tools::E('salt未创建');
        }
        return Tools::F()->password->build($password, $this->salt);
    }

    // 隐藏密码
    public function hiddenPassword()
    {
        $this->addHidden('password', 'salt');
        return $this;
    }

    // 关联账户数量
    public function getAccountCountAttribute()
    {
        return $this->account()->count();
    }
}