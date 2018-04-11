<?php

namespace Encore\Admin\Models\Task;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Tasktype_eav
 * @package App\Models
 * @version November 3, 2016, 1:12 pm CST
 */
class Attribute extends Model
{
    use SoftDeletes;


    protected $dates = ['deleted_at'];


    public $fillable = [
        'type_id',
        'code',
        'frontend_label',
        'frontend_input',
        'frontend_size',
        'not_list',
        'is_required',
        'is_unique',
        'is_report',
        'option',
        'user_id',
        'orderby',
        'note'
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'type_id' => 'integer',
        'code' => 'string',
        'frontend_label' => 'string',
        'frontend_input' => 'string',
        'frontend_size' => 'integer',
        'not_list' => 'integer',
        'is_required' => 'integer',
        'is_unique' => 'integer',
        'is_report' => 'integer',
        'option' => 'string',
        'orderby' => 'integer',
        'user_id' => 'integer',
        'note' => 'string'
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

    public function value()
    {
        return $this->hasMany(\Encore\Admin\Models\Task\Value::class, 'attribute_id', 'id');
    }
}
