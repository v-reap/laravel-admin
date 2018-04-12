<?php

namespace Encore\Admin\Models\Task;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
//use Illuminate\Support\Facades\DB;
//use Illuminate\Database\Eloquent\Builder;
//use Illuminate\Database\Eloquent\Relations\BelongsToMany;
//use Illuminate\Http\Request;
//use Illuminate\Support\Str;

/**
 * Class Task
 * @package App\Models
 * @version November 1, 2016, 11:12 am CST
 */
class Task extends Model
{
    use SoftDeletes;

    public $table = 'tasks';

    protected $dates = ['deleted_at'];

    public $fillable = [
        'title',
        'content',
        'time_limit',
        'price',
        'end_at',
        'task_id',
        'user_id',
        'status_id',
        'type_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'title' => 'string',
        'time_limit' => 'decimal',
        'price' => 'integer',
        'end_at' => 'datetime',
        'task_id' => 'integer',
        'user_id' => 'integer',
        'status_id' => 'integer',
        'type_id' => 'integer'
    ];

    /**
     * Validation rules
     *
     * @var array
     */
    public static $rules = [
        'title' => 'required',
        'user_id' => 'required',
        'status_id' => 'required',
        'type_id' => 'required',
        'end_at' => 'date_format:"Y-m-d H:i:s"',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function user()
    {
        return $this->belongsTo(\Encore\Admin\Auth\Database\Administrator::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function status()
    {
        return $this->belongsTo(\Encore\Admin\Models\Task\Status::class, 'status_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function type()
    {
        return $this->belongsTo(\Encore\Admin\Models\Task\Type::class, 'type_id', 'id');
    }

    public function value()
    {
        return $this->hasMany(\Encore\Admin\Models\Task\Value::class, 'task_id', 'id');
    }

//    protected static function boot()
//    {
//        parent::boot();
//
//        static::addGlobalScope('user_id', function(Builder $builder) {
//                $builder->orderBy('created_at', 'desc');
//        });
//    }
}
