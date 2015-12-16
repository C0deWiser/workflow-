<?php

namespace Media101\Workflow\Console;

use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Media101\Workflow\Contracts\WorkflowItem;
use Media101\Workflow\Models\Action;
use Media101\Workflow\Models\Entity;
use Media101\Workflow\Models\Feature;
use Media101\Workflow\Models\Relation;
use Media101\Workflow\Models\State;

class ReinitCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'workflow:reinit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize database metadata for the workflow extension reflecting the classes list changes.';

    /**
     * @var Connection
     */
    protected $db;

    /**
     * Create a new queue job table command instance.
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        parent::__construct();

        $this->db = $db;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        $classesInstances = $this->buildClassesInstances();
        $this->deleteExtraEntities($classesInstances->keys()->all());
        $missingInstances = $this->updateExistingEntities($classesInstances);
        $this->addEntities($missingInstances);

        $this->info('Done.');
    }

    /**
     * @return WorkflowItem[]|Collection
     */
    protected function buildClassesInstances()
    {
        return collect(config('workflow.classes'))->keyBy(function($class) {
            /* @var WorkflowItem $class */
            return $class::workflowName();
        })->map(function($class) {
            return new $class;
        });
    }

    /**
     * @param string[] $actualCodes
     */
    protected function deleteExtraEntities($actualCodes)
    {
        $entities = Entity::query()->where('code', 'NOT IN', $actualCodes)->getQuery()->select('id');
        $this->db->table(config('workflow.database.permissions_table'))
            ->where('entity_id', 'IN', $entities)->delete();
        Relation::query()->where('entity_id', 'IN', $entities)->delete();
        State::query()->where('entity_id', 'IN', $entities)->delete();
        Action::query()->where('entity_id', 'IN', $entities)->delete();
        Feature::query()->where('entity_id', 'IN', $entities)->delete();
        $entities->delete();
    }

    /**
     * @param WorkflowItem[]|Collection $classesInstances indexed by workflow names
     * @return WorkflowItem[]|Collection
     */
    protected function updateExistingEntities($classesInstances)
    {
        $existing = [];
        foreach (Entity::all() as $entity) {
            $this->updateEntity($entity, $classesInstances[$entity->code]);
            $existing[] = $entity->code;
        }
        return $classesInstances->except($existing);
    }

    /**
     * @param WorkflowItem[]|Collection $missingInstances
     */
    protected function addEntities($missingInstances)
    {
        foreach ($missingInstances as $code => $instance) {
            $entity = new Entity();
            $entity->code = $code;
            $entity->save();

            $this->updateEntity($entity, $instance);
        }
    }

    /**
     * @param Entity $entity
     * @param WorkflowItem $instance
     */
    protected function updateEntity(Entity $entity, WorkflowItem $instance)
    {
        $relations = Relation::query()->where('entity_id', '=', $entity->id);
        $states = State::query()->where('entity_id', '=', $entity->id);
        $features = Feature::query()->where('entity_id', '=', $entity->id);
        $actions = Action::query()->where('entity_id', '=', $entity->id);

        // delete extra permissions, actions, states, relations and features and add missing
        $permissions = $this->db->table(config('workflow.database.permissions_table'));

        foreach (['relation', 'state', 'feature', 'action'] as $kind) {
            $extra = ${'extra' . ucfirst($kind) . 's'} = clone ${$kind . 's'};
            /* @var Builder $extra */
            $extra->where('code', 'NOT IN', $instance->{'workflow' . ucfirst($kind) . 's'}());
            $permissions->orWhere($kind . '_id', 'IN', $extra->getQuery()->select('id'));
        }

        $permissions->where('entity_id', '=', $entity->id)->delete();

        foreach (['relation', 'state', 'feature', 'action'] as $kind) {
            ${'extra' . ucfirst($kind) . 's'}->delete();
            foreach (array_diff($instance->{'workflow' . ucfirst($kind) . 's'}(),
                    ${$kind . 's'}->get(['code'])->pluck('code')->all()) as $code) {
                $class = 'Media101\Workflow\Models\\' . ucfirst($kind);
                $item = new $class;
                $item->code = $code;
                $entity->{$kind . 's'}()->save($item);
            }
        }
    }
}
