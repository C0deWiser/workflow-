<?php

namespace Media101\Workflow\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Feature which item of the entity can possess
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
class Feature extends Model
{
    public function __construct(array $attributes = [])
    {
        $this->table = config('workflow.database.features_table');
        parent::__construct($attributes);
    }

    public function entity()
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    }
}
