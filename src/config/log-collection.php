<?php

return [
    /**
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log settings for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Settings: "single", "daily", "syslog", "errorlog"
    |
     */
    'log' => env('APP_LOG', 'daily'),

    /**
     * 日志位置,可以自己指定
     */
    'log_path' => env('LOG_COLLECTION_LOG_PATH',$app->storagePath().'/logs'),

    /**
     * 日志文件名称
     */
    'log_name' => env('LOG_NAME', 'laravel'),

    /**
     * 日志文件最大数
     */
    'log_max_files' => '30',
];
