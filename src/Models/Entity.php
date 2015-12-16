<?php

namespace Media101\Workflow\Models;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Entity represents the class under workflow access control.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 *
 * @property State[]|Collection $states
 * @property Relation[]|Collection $relations
 * @property Feature[]|Collection $features
 * @property Action[]|Collection $actions
 *
 * @package Media101\Workflow\Models
 */
class Entity extends Model
{
    /**
     * Whether or not to store timestamps in `created_at` and `updated_at`
     * @var bool
     */
    public $timestamps = false;

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

    protected static function boot(Connection $db = null)
    {
        parent::boot();

        static::deleting(function(Entity $entity) use($db) {
            $db->table(config('workflow.database.permissions_table'))
                ->where([ 'entity_id' => $entity->id ])->delete();

            $entity->relations()->delete();
            $entity->states()->delete();
            $entity->features()->delete();
            $entity->actions()->delete();
        });
    }
}
