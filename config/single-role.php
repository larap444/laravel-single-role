<?php

declare(strict_types = 1);

return [
    'models' => [
        'user' => 'App\User',
    ],

    'tables' => [
        'users'           => 'users',
        'roles'           => 'roles',
        'permissions'     => 'permissions',

        // Pivot tables.
        'permission_role' => 'permission_role',
        'permission_user' => 'permission_user',
    ],

    // Delimiter for passing roles and permissions as string.
    'delimiter' => '|',
];
