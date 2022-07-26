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
 * @property string $type
 * @property string $method
 * @property string $permission_id
 * @property string $operation
 * @property string $service_router
 * @property string $client_router
 * @property string $client_route_name
 * @property string $client_route_alias
 * @property string $icon
 * @property int $sort
 * @property-read Rule[] $children
 */
class Rule extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rule';
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
    protected $casts = ['id' => 'integer', 'pid' => 'integer', 'sort' => 'integer'];

    public $timestamps = false;

    public function children()
    {
        return $this->hasMany(Rule::class, 'pid', 'id');
    }

    public function setFormData(Body $input)
    {
        $this->pid = $input->pid ?: 0;
        $this->name = $input->name;
        $this->type = $input->type;
        $this->method = $input->method;
        $this->permission_id = $input->permission_id;
        $this->operation = $input->operation;
        $this->service_router = $input->service_router;
        $this->client_router = $input->client_router;
        $this->client_route_name = $input->client_route_name;
        $this->client_route_alias = $input->client_route_alias;
        $this->icon = $input->icon;
        $this->sort = $input->sort ?: 0;
    }

    public static function fullRulesIds($rule_ids)
    {
        $rules = Rule::query()->whereIn('id', $rule_ids)->get();
        $paths = [];
        array_map(function (Rule $rule) use (&$paths) {
            $paths = array_merge($paths, explode(',', $rule->path));
        }, $rules->all());
        return array_values(array_map(function ($id) {
            return (int)$id;
        }, array_filter(array_unique($paths))));
    }
}