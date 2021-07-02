<?php

declare (strict_types=1);

namespace lgdz\hyperf\model;

use Hyperf\DbConnection\Model\Model;
use lgdz\object\Body;

/**
 * @property int $id
 * @property int $pid
 * @property string $path
 * @property string $name
 * @property int $master
 * @property int $status
 * @property int $is_system
 * @property string $remark
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property  $rules
 * @property-read Role $parent
 * @property-read Role[] $children
 */
class Role extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'role';
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
    protected $casts = ['id' => 'integer', 'pid' => 'integer', 'master' => 'integer', 'status' => 'integer', 'is_system' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];

    public function parent()
    {
        return $this->belongsTo(Role::class, 'pid', 'id');
    }

    public function children()
    {
        return $this->hasMany(Role::class, 'pid', 'id');
    }

    public function getRulesAttribute($value)
    {
        return $value ? array_map(function ($id) {
            return (int)$id;
        }, json_decode($value, true)) : [];
    }

    public function setRulesAttribute($value)
    {
        $this->attributes['rules'] = json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function setFormData(Body $input, bool $isUpdate = false)
    {
        if (!$this->id) {
            $this->pid = $input->pid;
        }
        $this->name = $input->name;
        $this->status = $input->status;
        $this->remark = $input->remark;
        $this->rules = $input->rules;
    }
}