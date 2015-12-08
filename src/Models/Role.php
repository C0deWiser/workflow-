<?php

namespace Media101\Workflow\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * System-wide role which can be assigned to the user.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 *
 * @package Media101\Workflow\Models
 */
class Role extends Model
{
    public function __construct(array $attributes = [])
    {
        $this->table = config('workflow.database.roles_table');
        parent::__construct($attributes);
    }
}
