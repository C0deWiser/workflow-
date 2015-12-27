<?php

namespace Media101\Workflow\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Builder as QueryBuilder;
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
     * @var Repository
     */
    protected $cache;

    /**
     * Create a new queue job table command instance.
     * @param Connection $db
     * @param Repository $cache
     */
    public function __construct(Connection $db, Repository $cache)
    {
        parent::__construct();

        $this->db = $db;
        $this->cache = $cache;
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

        /**
         * Fuck, this method is not documented in the contract so the cache driver does not have to implement it.
         * @todo refactor
         */
        $this->cache->flush();

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
        $entities = Entity::query()->whereNotIn('code', $actualCodes)->getQuery()->select('id');

        $whereInEntities = function(QueryBuilder $queryBuilder) use ($entities) {
            $queryBuilder->select($entities->columns)->from($entities->from)
                ->mergeWheres($entities->wheres, $entities->getBindings());
        };

        /* @var QueryBuilder $entities */
        $query = State::query()->getQuery()->whereIn('entity_id', $whereInEntities);
        $this->db->table(config('workflow.database.permissions_table'))
            ->whereIn('entity_id', $whereInEntities)->delete();
        Relation::query()->whereIn('entity_id', $whereInEntities)->delete();
        State::query()->whereIn('entity_id', $whereInEntities)->delete();
        Action::query()->whereIn('entity_id', $whereInEntities)->delete();
        Feature::query()->whereIn('entity_id', $whereInEntities)->delete();
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
        $relations = Relation::query()->where('entity_id', $entity->id);
        $states = State::query()->where('entity_id', $entity->id);
        $features = Feature::query()->where('entity_id', $entity->id);
        $actions = Action::query()->where('entity_id', $entity->id);
        $extraRelations = $extraStates = $extraFeatures = $extraActions = null;

        // delete extra permissions, actions, states, relations and features and add missing
        $permissions = $this->db->table(config('workflow.database.permissions_table'))
                ->where('entity_id', $entity->id)
                ->where(function (QueryBuilder $builder) use ($relations, $states, $features, $actions, $instance,
                                                    &$extraRelations, &$extraStates, &$extraFeatures, &$extraActions) {
            foreach (['relation', 'state', 'feature', 'action'] as $kind) {
                $extra = ${'extra' . ucfirst($kind) . 's'} = clone ${$kind . 's'};
                /* @var Builder $extra */
                $extra->whereNotIn('code', $instance->{'workflow' . ucfirst($kind) . 's'}());
                $query = $extra->getQuery()->select('id');
                $builder->orWhereIn($kind . '_id', function(QueryBuilder $queryBuilder) use ($query) {
                    $queryBuilder->select($query->columns)->from($query->from)
                        ->mergeWheres($query->wheres, $query->getBindings());
                });
            }
        });

        $permissions->where('entity_id', $entity->id)->delete();

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
