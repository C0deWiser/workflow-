<?php

namespace Media101\Workflow\Models;

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
    public function __construct(array $attributes = [])
    {
        $this->table = config('workflow.database.actions_table');
        parent::__construct($attributes);
    }

    public function entity()
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    }
}
