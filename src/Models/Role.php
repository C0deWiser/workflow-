<?php

namespace Codewiser\Workflow\Models;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;

/**
 * System-wide role which can be assigned to the user.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 *
 * @package Codewiser\Workflow\Models
 */
class Role extends Model
{
    public $fillable = ['code', 'name'];

    /**
     * Whether or not to store timestamps in `created_at` and `updated_at`
     * @var bool
     */
    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        $this->table = config('workflow.database.roles_table');
        parent::__construct($attributes);
    }

    protected static function boot()
    {
        parent::boot();
        static::deleting(function(Role $role) {
            app(Connection::class)->table(config('workflow.database.permissions_table'))
                ->where('role_id', $role->id)->delete();
        });
    }
}
