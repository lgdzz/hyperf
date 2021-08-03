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
 * @property string $name
 * @property string $name_en
 * @property int $grade_id
 * @property int $len
 * @property int $status
 * @property int $sort
 * @property string $description
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @property-read Account[] $account
 * @property-read OrganizationGrade $grade
 * @property-read Role[] $role
 */
class Organization extends Model implements CacheableInterface
{
    use SoftDeletes;
    use Cacheable;

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

    public function setFormData(Body $input)
    {
        $pids = $input->pids ?? [];
        $pid = $pids[count($pids) - 1] ?? 0;
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
        }
        $this->pid = $pid;
        $this->pids = $pids;
        $this->len = $len;
        $this->name = $input->name;
        $this->name_en = Tools::F()->pinyin->initial($input->name);
        $this->grade_id = $input->grade_id;
        $this->status = $input->status;
        $this->sort = $input->sort ?? 0;
        $this->description = $input->description;
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