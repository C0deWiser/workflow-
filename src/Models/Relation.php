<?php

namespace Media101\Workflow\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Possible relation between the user and an item
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
class Relation extends Model
{
    public function __construct(array $attributes = [])
    {
        $this->table = config('workflow.database.relations_table');
        parent::__construct($attributes);
    }

    public function entity()
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    }
}
