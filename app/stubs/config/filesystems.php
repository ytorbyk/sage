<?php

return [
    'default' => 'dump',
    'disks' => [
        'dump' => [
            'driver' => 'local',
            'root' => config('env.db.dump_path'),
            'disable_asserts' => true
        ],
        'm2_configs' => [
            'driver' => 'local',
            'root' => config('env.m2.configs_path')
        ]
    ]
];
