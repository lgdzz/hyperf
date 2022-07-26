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
 * @property $path
 * @property int $len
 * @property string $code
 * @property string $name
 * @property string $name_en
 * @property string $description
 * @property int $sort
 * @property int $admin_role_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @property-read Role $role
 * @property $extends
 */
class OrganizationGrade extends Model implements CacheableInterface
{
    use SoftDeletes;
    use Cacheable;
    use ExtendTrait;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'organization_grade';
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
    protected $casts = ['id' => 'integer', 'pid' => 'integer', 'sort' => 'integer', 'admin_role_id' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    // 关联默认角色
    public function role()
    {
        return $this->belongsTo(Role::class, 'admin_role_id', 'id');
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

    public function setFormData(Body $input)
    {
        $pid = $input->pid ?? 1;
        if ($pid > 0) {
            $grade = OrganizationGrade::query()->where('id', $pid)->first();
            if (!$grade instanceof OrganizationGrade) {
                Tools::E('上级组织类型不存在');
            }
            $len = $grade->len + 1;
        } else {
            $len = 1;
        }
        $this->id && $this->id === $pid && Tools::E('上级组织类型不能选择当前类型');
        $this->pid = $pid;
        $this->len = $len;
        $this->code = $input->code ?: '';
        $this->name = $input->name;
        $this->name_en = Tools::F()->pinyin->initial($input->name);
        $this->sort = $input->sort ?? 0;
        $this->description = $input->description;
        $this->admin_role_id = $input->admin_role_id;
        $this->extends = $input->extends ?? [];
    }

    public function savePath()
    {
        $grade = OrganizationGrade::query()->where('id', $this->pid)->first();
        if ($grade instanceof OrganizationGrade) {
            $path = $grade->path;
            array_push($path, $this->id);
            $this->path = $path;
            $this->save();
        } else {
            $this->path = [$this->id];
            $this->save();
        }
    }
}