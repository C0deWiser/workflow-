<?php

namespace Media101\Workflow\Models;

use Illuminate\Database\Connection;
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
    /**
     * Whether or not to store timestamps in `created_at` and `updated_at`
     * @var bool
     */
    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        $this->table = config('workflow.database.features_table');
        parent::__construct($attributes);
    }

    public function entity()
    {
        return $this->belongsTo(Entity::class, 'entity_id');
    }

    protected static function boot(Connection $db = null)
    {
        parent::boot();
        static::deleting(function(Feature $feature) use($db) {
            $db->table(config('workflow.database.permissions_table'))
                ->where('feature_id', $feature->id)->delete();
        });
    }
}
