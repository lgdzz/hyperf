<?php

declare (strict_types=1);

namespace lgdz\hyperf\model;

use Hyperf\DbConnection\Model\Model;
use Hyperf\Database\Model\SoftDeletes;
use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;
use lgdz\hyperf\Tools;
use lgdz\object\Body;

/**
 * @property int $id
 * @property int $pid
 * @property string $pids
 * @property string $path
 * @property string $path_name
 * @property string $code
 * @property string $name
 * @property string $name_en
 * @property string $full_name
 * @property int $grade_id
 * @property int $len
 * @property int $status
 * @property int $sort
 * @property string $description
 * @property string $contact_name
 * @property string $contact_tel
 * @property string $contact_address
 * @property string $province_code
 * @property string $city_code
 * @property string $county_code
 * @property string $province
 * @property string $city
 * @property string $county
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @property-read Account[] $account
 * @property-read OrganizationGrade $grade
 * @property-read Role[] $role
 * @property $extends
 */
class Organization extends Model implements CacheableInterface
{
    use SoftDeletes;
    use Cacheable;
    use ExtendTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'organization';
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
    protected $casts = ['id' => 'integer', 'pid' => 'integer', 'grade_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    // 关联角色
    public function role()
    {
        return $this->hasMany(Role::class, 'org_id', 'id');
    }

    // 关联组织类型
    public function grade()
    {
        return $this->belongsTo(OrganizationGrade::class, 'grade_id', 'id');
    }

    // 关联账户
    public function account()
    {
        return $this->hasMany(Account::class, 'org_id', 'id');
    }

    public function setPidsAttribute($value)
    {
        return $this->attributes['pids'] = json_encode($value);
    }

    public function getPidsAttribute($value)
    {
        return json_decode($value, true);
    }

    public function setPathAttribute($value)
    {
        $this->attributes['path'] = implode(',', $value);
    }

    public function getPathAttribute($value)
    {
        return array_map(function ($item) {
            return (int)$item;
        }, explode(',', $value));
    }

    public function setPathNameAttribute($value)
    {
        $this->attributes['path_name'] = implode(',', $value);
    }

    public function getPathNameAttribute($value)
    {
        return explode(',', $value);
    }

    // 获取所有下级组织ID
    public function childIds(bool $self = false): array
    {
        return array_values(array_filter(array_map(function (Organization $row) use ($self) {
            if (!$self && $this->id === $row->id) {
                return null;
            } else {
                return $row->id;
            }
        }, Organization::query()->whereRaw("find_in_set({$this->id},path)")->get('id')->all())));
    }

    // 获取直接下级组织ID
    public function directChildIds(bool $self = false): array
    {
        $ids = array_values(array_filter(array_map(function (Organization $row) use ($self) {
            return $row->id;
        }, Organization::query()->where('pid', $this->id)->get('id')->all())));
        $self && array_unshift($ids, $this->id);
        return $ids;
    }

    // 直接下级组织列表
    public function directChildList($columns = ['*'])
    {
        return Organization::query()->where('pid', $this->id)->get($columns);
    }

    public function setFormData(Body $input)
    {
        $pid = $input->pid ?? Tools::Org()->id;
        if ($pid > 0) {
            $org = Organization::query()->where('id', $pid)->first();
            if (!$org instanceof Organization) {
                Tools::E('上级组织不存在');
            }
            $len = $org->len + 1;
            $pids = $org->pids;
            array_push($pids, $org->id);
        } else {
            $len = 1;
            $pids = [];
        }

        if ($input->code) {
            $this->code = $input->code;

            // 验证组织编码是否唯一
            if (config('lgdz.organization.code_unique', false)) {
                if ($this->id) {
                    if (Organization::query()->where('code', $this->code)->where('id', '!=', $this->id)->exists()) {
                        Tools::E('组织编号已存在');
                    }
                } else {
                    if (Organization::query()->where('code', $this->code)->exists()) {
                        Tools::E('组织编号已存在');
                    }
                }
            }

        } else {
            $this->code = '';
        }

        $this->id && $this->id === $pid && Tools::E('上级组织不能选择本单位');
        $this->pid = $pid;
        $this->pids = $pids;
        $this->len = $len;
        $this->name = $input->name;
        $this->name_en = Tools::F()->pinyin->initial($input->name);
        $this->full_name = $input->full_name ?: $this->name;
        $this->grade_id = $input->grade_id;
        $this->status = $input->status ?: 1;
        $this->sort = $input->sort ?: 0;
        $this->description = $input->description ?: '';
        $this->contact_name = $input->contact_name ?: '';
        $this->contact_tel = $input->contact_tel ?: '';
        $this->contact_address = $input->contact_address ?: '';
        $this->province_code = $input->province_code ?: '00';
        $this->province = $input->province ?: '';
        $this->city_code = $input->city_code ?: '00';
        $this->city = $input->city ?: '';
        $this->county_code = $input->county_code ?: '00';
        $this->county = $input->county ?: '';
//        $this->extends = $input->extends ?? [];
    }

    public function savePath()
    {
        $org = Organization::query()->where('id', $this->pid)->first();
        if ($org instanceof Organization) {
            $pids = $org->pids;
            array_push($pids, $org->id);
            $path = $org->path;
            array_push($path, $this->id);
            $path_name = $org->path_name;
            array_push($path_name, $this->name);
            $this->pids = $pids;
            $this->path = $path;
            $this->path_name = $path_name;
            $this->save();
        } else {
            $this->pids = [];
            $this->path = [$this->id];
            $this->path_name = [$this->name];
            $this->save();
        }
    }
}