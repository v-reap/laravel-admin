<?php

namespace Encore\Admin\Auth\Database;

use Encore\Admin\Traits\AdminBuilder;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Administrator.
 *
 * @property Role[] $roles
 */
class Administrator extends Model implements AuthenticatableContract
{
    use Authenticatable, AdminBuilder, HasPermissions;

    protected $fillable = ['username', 'password', 'name', 'tel', 'leader_id', 'avatar'];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $connection = config('admin.database.connection') ?: config('database.default');

        $this->setConnection($connection);

        $this->setTable(config('admin.database.users_table'));

        parent::__construct($attributes);
    }

    public function assignableUser()
    {
        $team = $this->where('leader_id','=',$this->leader_id)->pluck('name','id');
        return $team;
//        return $this->getTeamUser()->first() ? $this->getTeamUser()->first() : [$this->id=>$this->name,];
    }

    public function getTeamUser()
    {
        $roles = $this->roles;
        if ($roles){
            $depts = $roles->where('leader_id','=',$this->leader_id);
            $teamUser = [];
            foreach ($depts as $dept) {
                $dept->administrators ? $teamUser[] = $dept->administrators->pluck('name','id') : [];
            }
            return collect($teamUser);
        }
        return null;
    }

    public function isLeader()
    {
        $roles = $this->roles;
        if ($roles){
            $depts = $roles->where('leader_id','=',$this->id);
            return $depts ? true : false;
        }
        return false;
    }

    public function leader()
    {
        $relatedModel = config('admin.database.users_model');
        return $this->belongsTo($relatedModel, 'leader_id', 'id');
    }
}
