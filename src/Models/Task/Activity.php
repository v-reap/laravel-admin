<?php

namespace Encore\Admin\Models\Task;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Tasktype_eav
 * @package App\Models
 * @version November 3, 2016, 1:12 pm CST
 */
class Activity extends Model
{

    public $fillable = [
        'name',
        'type_id',
        'parent_id',
        'user_id',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'type_id' => 'integer',
        'parent_id' => 'integer',
        'user_id' => 'integer',
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function type()
    {
        return $this->belongsTo(\Encore\Admin\Models\Task\Type::class, 'type_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function user()
    {
        return $this->belongsTo(\Encore\Admin\Auth\Database\Administrator::class, 'user_id', 'id');
    }

    public function parent()
    {
        return $this->hasMany(static::class, 'parent_id', 'id');
    }

    public function action()
    {
        return $this->hasMany(\Encore\Admin\Models\Task\Action::class, 'activity_id', 'id');
    }
}
