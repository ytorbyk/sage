<?php

return [
    'default' => 'm2_configs',
    'disks' => [
        'm2_configs' => [
            'driver' => 'local',
            'root' => config('env.m2.configs_path')
        ]
    ]
];
