<?php

namespace Encore\Admin\Models\Task;

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Facades\Admin;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
//use Illuminate\Support\Facades\DB;
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

    public $atts;

    protected $dates = ['deleted_at'];

    public $fillable = [
        'title',
        'content',
        'time_limit',
        'price',
        'end_at',
        'root_id',
        'next_id',
        'last_id',
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
        'root_id' => 'integer',
        'next_id' => 'integer',
        'last_id' => 'integer',
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

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function user()
    {
        return $this->belongsTo(Administrator::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function status()
    {
        return $this->belongsTo(Status::class, 'status_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     **/
    public function type()
    {
        return $this->belongsTo(Type::class, 'type_id', 'id');
    }

    public function value()
    {
        return $this->hasMany(Value::class, 'task_id', 'id');
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

//    public function getAttrs()
//    {
//        if (!$this->atts){
//            $this->atts = Attribute::where('type_id','=',$this->attributes['type_id'])->get();
//        }
//        return $this->atts;
//    }

//    public function isEavAttrs($key)
//    {
//        return isset($this->attributes['type_id'])
//                        && $this->attributes['type_id']
//                        && $this->getAttrs()
//                        && !$this->getAttribute($key);
//    }

//    public function __get($key)
//    {
//        if($this->isEavAttrs($key)){
//            $attr = $this->atts ? $this->atts->firstWhere('code','=',$key) : null;
//            $attrArray = $attr ? $attr->toArray() : [];
//            if ($attrArray) {
//                $value = Value::where('task_id','=',$this->attributes['id'])->where('attribute_id','=',$attrArray['id'])->first();
//                $this->attributes[$key] = $value ? $value->task_value : null;
//            }
//        }
//
//        return $this->getAttribute($key);
//    }
//
//    public function __set($key, $value)
//    {
//        if($this->isEavAttrs($key)){
//            $attr = $this->atts ? $this->atts->firstWhere('code','=',$key) : null;
//            $attrArray = $attr ? $attr->toArray() : [];
//            if ($attrArray) {
//                $value = Value::updateOrCreate(['task_id'=>$this->attributes['id'],'attribute_id'=>$attrArray['id']],['task_value'=>$value])->first();
//                $this->attributes[$key] = $value ? $value->task_value : null;
//            }
//        }
//        $this->setAttribute($key, $value);
//    }

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('user_id', function(Builder $builder) {
//            $user = Admin::user();
//            if(!$user->isAdministrator()){
//                $builder->where('user_id', '=', $user->id);
//            }
//            else if (1){
//                $builder->whereIn('user_id', $user);
//            }
            $builder->orderBy('created_at', 'desc');
        });
    }
}
