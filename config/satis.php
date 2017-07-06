<?php

$filesystem = new \Illuminate\Filesystem\Filesystem();

$sharedConfig = json_decode($filesystem->get(base_path('node/config.json')), true);

return [
    'config' => base_path(env('SATIS_CONFIG', 'resources' . DIRECTORY_SEPARATOR . 'satis.json')),
    'composer_home' => base_path(env('COMPOSER_HOME', 'storage' . DIRECTORY_SEPARATOR . 'composer')),
    'composer_cache' => base_path(env('COMPOSER_HOME', 'storage' . DIRECTORY_SEPARATOR . 'composer/cache')),

    // See https://www.selenic.com/mercurial/hg.1.html#environment-variables for how HGRCPATH can be set/used
    'hgrc_path' => false,

    'memory_limit' => '2G',
    'build_verbosity' => 'vvv',

    'private_repository' => 'private',
    'public_repository' => 'public',

    'proxy' => [
        'http' => env('SATIS_HTTP_PROXY', null),
        'https' => env('SATIS_HTTPS_PROXY', null)
    ],

    /**
     *
     * Edit these options at your own risk
     *
     **/
    'default_repository_type' => 'vcs',

    'repository_types' => [
        'vcs', 'hg', 'pear', 'composer', 'artifact', 'path'
    ],

    'build_directory' => base_path('public'),

    'public_mirror' => storage_path('app' . DIRECTORY_SEPARATOR . 'public.json'),
    'private_mirror' => storage_path('app' . DIRECTORY_SEPARATOR . 'private.json'),

    'lock' => base_path($sharedConfig['lock_file']),

    'node' => $sharedConfig,

    'sync_timeout' => 120,

    'webpack_dev_server' => env('WEBPACK_DEV_SERVER', 'http://localhost:9001'),
];
