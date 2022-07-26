<?php

declare (strict_types=1);

namespace lgdz\hyperf\model;

use Hyperf\DbConnection\Model\Model;

/**
 * @property int $id
 * @property int $org_id
 * @property int $user_id
 * @property int $account_id
 * @property string $title
 * @property string $operator
 * @property string $method
 * @property string $path
 * @property string $body
 * @property \Carbon\Carbon $created_at
 */
class Oplog extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'oplog';
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
    protected $casts = ['id' => 'integer', 'org_id' => 'integer', 'user_id' => 'integer', 'account_id' => 'integer'];

    const UPDATED_AT = null;

    public function setBodyAttribute($value)
    {
        return $this->attributes['body'] = json_encode($value, JSON_UNESCAPED_UNICODE);
    }

    public function getBodyAttribute($value)
    {
        return json_decode($value, true);
    }
}