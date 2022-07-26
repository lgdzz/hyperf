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
 * @property int $org_id
 * @property int $len
 * @property string $name
 * @property string $code
 * @property int $sort
 * @property int $status
 * @property array $extends
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string $deleted_at
 * @property  $path
 * @property-read Organization $org
 * @property-read int $member_count
 */
class Department extends Model implements CacheableInterface
{
    use SoftDeletes;
    use Cacheable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'department';
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
    protected $casts = ['id' => 'integer', 'pid' => 'integer', 'org_id' => 'integer', 'len' => 'integer', 'sort' => 'integer', 'status' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    public function org()
    {
        return $this->belongsTo(Organization::class, 'org_id', 'id');
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

    public function setExtendsAttribute($value)
    {
        $this->attributes['extends'] = json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function getExtendsAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    // 获取所有下级部门ID
    public function childIds(bool $self = false): array
    {
        return array_values(array_filter(array_map(function (Department $row) use ($self) {
            if (!$self && $this->id === $row->id) {
                return null;
            } else {
                return $row->id;
            }
        }, Department::query()->whereRaw("find_in_set({$this->id},path)")->get('id')->all())));
    }

    // 获取直接下级部门ID
    public function directChildIds(bool $self = false): array
    {
        $ids = array_values(array_filter(array_map(function (Department $row) use ($self) {
            return $row->id;
        }, Department::query()->where('pid', $this->id)->get('id')->all())));
        $self && array_unshift($ids, $this->id);
        return $ids;
    }

    // 部门人数
    public function getMemberCountAttribute(): int
    {
        return DepartmentMember::query()->where('department_id', $this->id)->count();
    }

    public function setFormData(Body $input)
    {
        if (!$this->id) {
            $this->org_id = $input->org_id ?: Tools::Org()->id;
        }
        $pid = $input->pid ?: 0;
        if ($pid > 0) {
            $department = Department::query()->where('id', $pid)->first();
            if (!$department instanceof Department) {
                Tools::E('上级部门不存在');
            }
            $len = $department->len + 1;
        } else {
            $len = 1;
        }

        if ($input->code) {
            $this->code = $input->code;
            Department::query()->where('code', $this->code)->where('id', '!=', $this->id)->exists() && Tools::E('部门编码已存在');
        }

        $this->id && $this->id === $pid && Tools::E('上级部门不能选择本部门');
        $this->pid = $pid;
        $this->len = $len;
        $this->name = $input->name;
        $this->status = $input->status ?: 1;
        $this->sort = $input->sort ?: 255;
        $this->extends = $input->extends ?: [];
    }

    public function savePath()
    {
        $department = Department::query()->where('id', $this->pid)->first();
        if ($department instanceof Department) {
            $path = $department->path;
            array_push($path, $this->id);
            $this->path = $path;
        } else {
            $this->path = [$this->id];
        }
        $this->save();
    }
}