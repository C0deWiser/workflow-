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

        'role_user_table'   => 'workflow_user_role',

    ],

    'defaults' => [

        'actions'           => [ 'view', 'edit', 'delete' ],

    ],

    // List all the classes here. The list will be used when reinitializing workflow metadata.
    'classes' => [

    ],
];
