<?php

return [
    'database' => [
        'permissions_table' => 'workflow_permissions',
        'entities_table'    => 'workflow_entities',
        'states_table'      => 'workflow_states',
        'roles_table'       => 'workflow_roles',
        'relations_table'   => 'workflow_relations',
        'features_table'    => 'workflow_features',
        'actions_table'     => 'workflow_actions',
    ],
    'defaults' => [
        'actions' => [
            'view', 'edit', 'delete',
        ],
    ],
];
