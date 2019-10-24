<?php

namespace Codewiser\Workflow\Models;

use Illuminate\Database\Connection;
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
 * @package Codewiser\Workflow\Models
 */
class Relation extends Model
{
    /**
     * Whether or not to store timestamps in `created_at` and `updated_at`
     * @var bool
     */
    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        $this->table = config('workflow.database.relations_table');
        parent::__construct($attributes);
    }

    public function entity()
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    }

    protected static function boot(Connection $db = null)
    {
        parent::boot();
        static::deleting(function(Relation $relation) use($db) {
            $db->table(config('workflow.database.permissions_table'))
                ->where('relation_id', $relation->id)->delete();
        });
    }
}
