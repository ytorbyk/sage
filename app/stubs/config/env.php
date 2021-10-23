<?php

return [
    'services' => [
        'dns',
        'apache',
        'mysql',
        'memcached',
        'redis',
        'mailhog',
        'elasticsearch',
        'kibana',
        'rabbitmq'
    ],

    'dns' => [
        'formula' => 'dnsmasq',
        'domains' => ['test'],
        'config_path' => config('env.home') . DIRECTORY_SEPARATOR . 'dnsmasq.conf',
        'brew_config_path' => '/usr/local/etc/dnsmasq.conf',
        'brew_config_dir_path' => '/usr/local/etc/dnsmasq.d',
        'resolver_path' => '/etc/resolver',
    ],

    'mysql' => [
        'formula' => 'mysql@5.7',
        'password' => '1',
        'brew_config_path' => '/usr/local/etc/my.cnf',
        'data_dir_path' => '/usr/local/var/mysql'
    ],

    'progress' => [
        'formula' => 'pv'
    ],

    'db' => [
        'dump_path' => config('env.home_public') . DIRECTORY_SEPARATOR . 'databases',
    ],

    'apache' => [
        'formula' => 'httpd',
        'vhosts' => config('env.home') . DIRECTORY_SEPARATOR . 'apache-vhosts',
        'config' => config('env.home') . DIRECTORY_SEPARATOR . 'httpd.conf',
        'brew_config_path' => '/usr/local/etc/httpd/httpd.conf',
        'brew_config_dir_path' => '/usr/local/etc/httpd',
        'localhost_path' => config('env.home_public') . DIRECTORY_SEPARATOR . 'localhost',
        'php_module' => 'LoadModule php{high_version}_module /usr/local/opt/php@{version}/lib/httpd/modules/libphp{high_version}.so',
        'php_module_header' => '#Load PHP Module',
    ],

    'php' => [
        'main_version' => '8.0',
        'brew_path' => '/usr/local/bin/php',
        'brew_etc_path' => '/usr/local/etc/php',
        'brew_lib_path' => '/usr/local/lib/php',
        'brew_pear_path' => '/usr/local/share/pear',
        'dependencies' => [
            'autoconf',
            'pkg-config',
            'imagemagick',
            'zlib',
        ],
        'taps' => [
            '5.6' => 'shivammathur/php',
            '7.0' => 'shivammathur/php',
            '7.1' => 'shivammathur/php',
            '7.2' => 'shivammathur/php',
            '7.3' => 'shivammathur/php',
            '7.4' => 'shivammathur/php',
            '8.0' => 'shivammathur/php',
            '8.1' => 'shivammathur/php',
        ],
        'versions' => [
            '5.6',
            '7.0',
            '7.1',
            '7.2',
            '7.3',
            '7.4',
            '8.0',
            '8.1',
        ],
        'smtp_catcher' => 'files',
        'smtp_catcher_mailhog' => '/usr/local/bin/MailHog sendmail no@email',
        'mail_path' => config('env.home_public') . DIRECTORY_SEPARATOR . 'mail',
        'smtp_catcher_files' => config('env.home') . DIRECTORY_SEPARATOR . 'smtp_catcher.php',
    ],

    'memcached' => [
        'formula' => 'memcached',
        'dependencies' => [
            'libmemcached',
        ]
    ],

    'elasticsearch' => [
        'formula' => 'elasticsearch-full',
        'plugins' => [
            'analysis-phonetic',
            'analysis-icu'
        ],
        'brew_config_dir_path' => '/usr/local/etc/elasticsearch',
        'data_dir_path' => '/usr/local/var/elasticsearch',
        'log_dir_path' => '/usr/local/var/log/elasticsearch/'
    ],

    'kibana' => [
        'formula' => 'kibana-full',
        'domain' => 'kibana.test'
    ],

    'mailhog' => [
        'formula' => 'mailhog',
        'domain' => 'mailhog.test',
        'log_path' => '/usr/local/var/log/mailhog.log'
    ],

    'rabbitmq' => [
        'formula' => 'rabbitmq',
        'domain' => 'rabbitmq.test',
        'brew_config_dir_path' => '/usr/local/etc/rabbitmq',
        'brew_lib_dir_path' => '/usr/local/var/lib/rabbitmq',
        'log_dir_path' => '/usr/local/var/log/rabbitmq'
    ],

    'redis' => [
        'formula' => 'redis'
    ],

    'secure' => [
        'formula' => 'openssl',
        'certificates_path' => config('env.home') . DIRECTORY_SEPARATOR . 'certificates',
        'securable_domain' => '.test',
        'secured_domains' => [],
    ],

    'completion' => [
        'formula' => 'bash-completion',
        'brew_config_completion_path' => '/usr/local/etc/bash_completion.d' . DIRECTORY_SEPARATOR . strtolower(config('app.name')),
        'brew_completion' => 'source $(brew --prefix)/etc/bash_completion',
        'bashrc_path' => env('HOME') . DIRECTORY_SEPARATOR . '.bashrc',
        'bash_profile_path' => env('HOME') . DIRECTORY_SEPARATOR . '.bash_profile'
    ],

    'software' => [
        'git',
        'composer',
        'bash'
    ],

    'm2' => [
        'configs_path' => config('env.home_public') . DIRECTORY_SEPARATOR . 'm2' . DIRECTORY_SEPARATOR . 'configs',
    ],

    'backup_path' => config('env.home_public') . DIRECTORY_SEPARATOR . 'backups',
    'logs_path' => config('env.home_public') . DIRECTORY_SEPARATOR . 'logs',
    'tmp_path' => config('env.home') . DIRECTORY_SEPARATOR . 'tmp',
];
