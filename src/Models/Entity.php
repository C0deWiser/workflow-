<?php

namespace Media101\Workflow\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Entity represents the class under workflow access control.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 *
 * @property State[] $states
 * @property Relation[] $relations
 * @property Feature[] $features
 * @property Action[] $actions
 *
 * @package Media101\Workflow\Models
 */
class Entity extends Model
{
    public function __construct(array $attributes = [])
    {
        $this->table = config('workflow.database.entities_table');
        parent::__construct($attributes);
    }

    public function states()
    {
        return $this->hasMany(State::class, 'entity_id');
    }

    public function relations()
    {
        return $this->hasMany(Relation::class, 'entity_id');
    }

    public function features()
    {
        return $this->hasMany(Feature::class, 'entity_id');
    }

    public function actions()
    {
        return $this->hasMany(Action::class, 'entity_id');
    }
}
