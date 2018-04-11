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
        'multi_assigned',
        'project_required',
        'product_required',
        'comment_required',
        'user_id',
        'type_id',
        'bentity_id'  //huayan
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
        'multi_assigned' => 'integer',
        'project_required' => 'integer',
        'product_required' => 'integer',
        'comment_required' => 'integer',
        'user_id' => 'integer',
        'type_id'=> 'string'
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
