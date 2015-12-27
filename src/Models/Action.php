<?php

namespace Media101\Workflow\Models;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;

/**
 * Action which can be performed an entity item.
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
class Action extends Model
{
    /**
     * Whether or not to store timestamps in `created_at` and `updated_at`
     * @var bool
     */
    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        $this->table = config('workflow.database.actions_table');
        parent::__construct($attributes);
    }

    public function entity()
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    }

    protected static function boot(Connection $db = null)
    {
        parent::boot();
        static::deleting(function(Action $action) use($db) {
            $db->table(config('workflow.database.permissions_table'))
                ->where('action_id', $action->id)->delete();
        });
    }
}
