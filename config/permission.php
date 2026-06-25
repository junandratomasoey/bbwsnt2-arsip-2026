<?php

return [

    'models' => [
        // Ganti dengan model kustom kita yang pakai UUID
        'permission' => App\Models\Permission::class,
        'role'       => App\Models\Role::class,
    ],

    'table_names' => [
        'roles'                 => 'roles',
        'permissions'           => 'permissions',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles'       => 'model_has_roles',
        'role_has_permissions'  => 'role_has_permissions',
    ],

    'column_names' => [
        'role_pivot_key'       => 'role_id',
        'permission_pivot_key' => 'permission_id',

        // WAJIB diubah ke 'model_uuid' agar cocok dengan migration kita
        'model_morph_key'      => 'model_uuid',

        'team_foreign_key'     => 'team_id',
    ],

    'register_permission_check_method' => true,
    'register_octane_reset_listener'   => false,

    'teams' => false,

    'vehicles' => [],

    'cache' => [
        'expiration_time'  => \DateInterval::createFromDateString('24 hours'),
        'key'              => 'spatie.permission.cache',
        'store'            => 'default',
    ],
];
