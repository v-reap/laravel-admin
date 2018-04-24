<?php

namespace Encore\Admin\Models\Task;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Tasktype
 * @package App\Models
 * @version November 4, 2016, 1:48 pm CST
 */
class Type extends Model
{
    use SoftDeletes;

    protected $dates = ['deleted_at'];


    public $fillable = [
        'name',
        'color',
        'assigned_to',
        'root_id',
        'last_id',
        'next_id',
        'is_approvable',
        'rules',
        'user_id'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'color' => 'string',
        'assigned_to' => 'integer',
        'root_id' => 'integer',
        'last_id' => 'integer',
        'next_id' => 'integer',
        'is_approvable' => 'integer',
        'rules' => 'string',
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
    public function assignedto()
    {
        return $this->belongsTo(\Encore\Admin\Auth\Database\Administrator::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function user()
    {
        return $this->belongsTo(\Encore\Admin\Auth\Database\Administrator::class, 'user_id', 'id');
    }

    public function task()
    {
        return $this->hasMany(\Encore\Admin\Models\Task\Task::class, 'type_id', 'id');
    }

    public function root()
    {
        return $this->belongsTo(static::class, 'root_id', 'id');
    }

    public function last()
    {
        return $this->belongsTo(static::class, 'last_id', 'id');
    }

    public function next()
    {
        return $this->belongsTo(static::class, 'next_id', 'id');
    }

    public function attribute()
    {
        return $this->hasMany(\Encore\Admin\Models\Task\Attribute::class, 'type_id', 'id');
    }

    protected static function boot()
    {
        parent::boot();

//        static::updating(function ($model) {
//            return false;
//        });
//        static::addGlobalScope('user_id', function(Builder $builder) {
//            $user = \Auth::user();
//            if(!$user->isAdmin()){
//                $builder->where('user_id', '=', 0)->orWhere('user_id', '=', $user->id)->orWhere('user_id', '=', $user->leader);
//            }
//        });
    }

}
