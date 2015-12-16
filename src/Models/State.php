<?php

namespace Media101\Workflow\Models;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;

/**
 * Workflow state some entity can have
 *
 * @property int $id
 * @property int $entity_id
 * @property string $code
 * @property string $name
 *
 * @property Entity $entity
 *
 * @package Media101\Workflow\Models
 */
class State extends Model
{
    /**
     * Whether or not to store timestamps in `created_at` and `updated_at`
     * @var bool
     */
    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        $this->table = config('workflow.database.states_table');
        parent::__construct($attributes);
    }

    public function entity()
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    }

    protected static function boot(Connection $db = null)
    {
        parent::boot();
        static::deleting(function(State $state) use($db) {
            $db->table(config('workflow.database.permissions_table'))
                ->where(['state_id' => $state->id])->delete();
        });
    }
}
