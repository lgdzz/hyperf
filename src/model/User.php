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
 * @property string $type
 * @property string $phone
 * @property string $realname
 * @property string $username
 * @property string $password
 * @property string $salt
 * @property int $status
 * @property int $is_system
 * @property string $remark
 * @property string $last_ip
 * @property string $last_time
 * @property string $from_channel
 * @property int $from_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read Account[] $account
 * @property-read int $account_count
 * @property $extends
 */
class User extends Model implements CacheableInterface
{
    use SoftDeletes;
    use Cacheable;
    use ExtendTrait;

    // 账号锁定
    const LOCK = 2;

    // 来源渠道
    const FROM_CHANNEL_ORG = '组织';

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
    protected $casts = ['id' => 'integer', 'status' => 'integer', 'is_system' => 'integer', 'from_id' => 'integer', 'last_time' => 'datetime', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

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

    // 检查密码强度
    public function checkPasswordStrength(string $password)
    {
        try {
            Tools::F()->password->checkStrength($password, $this->username);
        } catch (\Exception $e) {
            Tools::E($e->getMessage());
        }
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

    // 易读最后登录时间
    public function getLastTimeFormatAttribute()
    {
        try {
            return $this->last_time ? Tools::F()->time->easyReadString($this->last_time->format('Y-m-d H:i:s')) : '-';
        } catch (\Throwable $e) {
            return '-';
        }
    }

    // 检查手机唯一性
    public function checkPhoneUnique(string $phone)
    {
        return User::query()->where('phone', $phone)->where('id', '!=', $this->id)->first() && Tools::E("手机号[{$phone}]已注册");
    }
}