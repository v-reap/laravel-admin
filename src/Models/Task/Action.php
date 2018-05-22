<?php
namespace Encore\Admin\Models\Task;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Tasktype_eav_value
 * @package App\Models
 * @version November 3, 2016, 1:15 pm CST
 */
class Action extends Model
{

    public $fillable = [
        'title',
        'activity_id',
        'user_id',
        'task_id',
        'type_id',
        'is_done'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'title' => 'string',
        'activity_id' => 'integer',
        'user_id' => 'integer',
        'task_id' => 'integer',
        'type_id' => 'integer',
        'is_done' => 'integer'
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
    public function user()
    {
        return $this->belongsTo(\Encore\Admin\Auth\Database\Administrator::class, 'user_id', 'id');
    }

    public function task()
    {
        return $this->belongsTo(\Encore\Admin\Models\Task\Task::class, 'task_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function activity()
    {
        return $this->belongsTo(\Encore\Admin\Models\Task\Activity::class, 'activity_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function type()
    {
        return $this->belongsTo(Type::class, 'type_id', 'id');
    }
}
