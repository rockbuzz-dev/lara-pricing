<?php

use Rockbuzz\LaraRbac\Models\{Role, Permission};

return [
    'models' => [
        'role' => Role::class,
        'permission' => Permission::class,
    ],
    'tables' => [
        'prefix' => '',
        'morph_columns' => [
            'id' => 'resource_id',
            'type' => 'resource_type'
        ]
    ],
    'positive_values' => ['Y', 'OK', 'TRUE']
];
