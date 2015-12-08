<?php

namespace Media101\Workflow\Models;

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
    public function __construct(array $attributes = [])
    {
        $this->table = config('workflow.database.states_table');
        parent::__construct($attributes);
    }

    public function entity()
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    }
}
