<?php

return [
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

    'apache' => [
        'formula' => 'httpd',
        'vhosts' => config('env.home') . DIRECTORY_SEPARATOR . 'apache-vhosts',
        'config' => config('env.home') . DIRECTORY_SEPARATOR . 'httpd.conf',
        'brew_config_path' => '/usr/local/etc/httpd/httpd.conf',
        'brew_config_dir_path' => '/usr/local/etc/httpd',
        'localhost_path' => config('env.home') . DIRECTORY_SEPARATOR . 'sites' . DIRECTORY_SEPARATOR . 'localhost',
        'php_module' => 'LoadModule php{high_version}_module /usr/local/opt/php@{version}/lib/httpd/modules/libphp{high_version}.so',
        'php_module_header' => '#Load PHP Module',
    ],

    'php' => [
        'main_version' => '7.2',
        'brew_path' => '/usr/local/bin/php',
        'brew_etc_path' => '/usr/local/etc/php',
        'brew_lib_path' => '/usr/local/lib/php',
        'brew_pear_path' => '/usr/local/share/pear',
        'versions' => [
            '5.6',
            '7.0',
            '7.1',
            '7.2'
        ],
        'mail_path' => env('HOME') . DIRECTORY_SEPARATOR . 'Support' . DIRECTORY_SEPARATOR . 'mail',
        'smtp_catcher_path' => config('env.home') . DIRECTORY_SEPARATOR . 'smtp_catcher.php',
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

    'logs_path' => config('env.home') . DIRECTORY_SEPARATOR . 'logs',
    'tmp_path' => config('env.home') . DIRECTORY_SEPARATOR . 'tmp',
];
