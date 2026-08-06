<?php

use Illuminate\Support\Str;

return [

    /*
    |--------------------------------------------------------------------------
    | Horizon Domain
    |--------------------------------------------------------------------------
    | You may change the domain Horizon sits on. When null, Horizon will use
    | the default domain for the application.
    */
    'domain' => env('HORIZON_DOMAIN', null),

    /*
    |--------------------------------------------------------------------------
    | Horizon Path
    |--------------------------------------------------------------------------
    | The path prefix under which the Horizon dashboard will be accessible.
    | You are free to change this path to whatever you like.
    */
    'path' => env('HORIZON_PATH', 'horizon'),

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Connection
    |--------------------------------------------------------------------------
    | The Redis connection that Horizon will use when storing job metrics,
    | tags, supervisor records, etc. Typically "default" will work fine.
    */
    'use' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Horizon Redis Prefix
    |--------------------------------------------------------------------------
    | A prefix for all Redis keys that Horizon creates. This can be handy
    | in multi-tenant or multi-app scenarios sharing the same Redis instance.
    */
    'prefix' => env(
        'HORIZON_PREFIX',
        Str::slug(env('APP_NAME', 'laravel'), '_') . '_horizon:'
    ),

    /*
    |--------------------------------------------------------------------------
    | Horizon Route Middleware
    |--------------------------------------------------------------------------
    | These middleware will be applied to every Horizon route. Add your own
    | middleware here to guard the dashboard.
    */
    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Queue Wait Time Thresholds
    |--------------------------------------------------------------------------
    | Defines the wait-time thresholds (seconds) that trigger a "long wait"
    | notification per queue. If a queue exceeds its threshold, Horizon alerts.
    */
    'waits' => [
        'redis:default'       => 60,
        'redis:notifications' => 30,
        'redis:high'          => 20,
        'redis:low'           => 120,
    ],

    /*
    |--------------------------------------------------------------------------
    | Job Trimming
    |--------------------------------------------------------------------------
    | How many minutes Horizon stores completed / failed / monitored job info.
    */
    'trim' => [
        'recent'        => 60,     // 1 hour
        'pending'       => 60,
        'completed'     => 60,
        'recent_failed' => 10080,  // 7 days
        'failed'        => 10080,
        'monitored'     => 10080,
    ],

    /*
    |--------------------------------------------------------------------------
    | Silenced Jobs
    |--------------------------------------------------------------------------
    | Jobs in this list will not appear in the Horizon dashboard. Useful for
    | very high-frequency polling jobs that would clutter the UI.
    */
    'silenced' => [
        // App\Jobs\SomeHighVolumeJob::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Metrics
    |--------------------------------------------------------------------------
    | How many data points Horizon keeps for throughput / runtime graphs.
    */
    'metrics' => [
        'trim_snapshots' => [
            'job'   => 24,
            'queue' => 24,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fast Termination
    |--------------------------------------------------------------------------
    | If enabled, Horizon terminates its master process as soon as the last
    | supervisor stops, rather than waiting for all workers to finish.
    */
    'fast_termination' => false,

    /*
    |--------------------------------------------------------------------------
    | Memory Limit (MB)
    |--------------------------------------------------------------------------
    | Horizon will terminate and restart any worker that exceeds this limit.
    */
    'memory_limit' => 64,

    /*
    |--------------------------------------------------------------------------
    | Queue Worker Configuration
    |--------------------------------------------------------------------------
    | Configure supervisors (groups of queue workers) per environment.
    | Each supervisor can be tuned for a specific queue or workload type.
    |
    | Naming convention: <env>-<purpose>
    */
    'defaults' => [
        'supervisor-1' => [
            'connection'   => 'redis',
            'queue'        => ['default'],
            'balance'      => 'auto',
            'autoScaling'  => false,
            'minProcesses' => 1,
            'maxProcesses' => 8,
            'balanceMaxShift'   => 1,
            'balanceCooldown'   => 3,
            'tries'        => 3,
            'timeout'      => 90,
            'nice'         => 0,
        ],
    ],

    'environments' => [

        // ── Production ─────────────────────────────────────────────────
        'production' => [

            // Default queue — balanced, scales 2–8 workers
            'supervisor-default' => [
                'connection'   => 'redis',
                'queue'        => ['default', 'low'],
                'balance'      => 'auto',
                'autoScaling'  => false,
                'minProcesses' => 2,
                'maxProcesses' => 8,
                'balanceMaxShift'   => 1,
                'balanceCooldown'   => 3,
                'tries'        => 3,
                'timeout'      => 90,
                'nice'         => 0,
            ],

            // Notifications queue — dedicated workers so alerts are never delayed
            'supervisor-notifications' => [
                'connection'   => 'redis',
                'queue'        => ['notifications'],
                'balance'      => 'simple',
                'autoScaling'  => false,
                'minProcesses' => 2,
                'maxProcesses' => 4,
                'tries'        => 3,
                'timeout'      => 60,
                'nice'         => 0,
            ],

            // High-priority queue (imports, report generation, etc.)
            'supervisor-high' => [
                'connection'   => 'redis',
                'queue'        => ['high'],
                'balance'      => 'simple',
                'autoScaling'  => false,
                'minProcesses' => 1,
                'maxProcesses' => 4,
                'tries'        => 3,
                'timeout'      => 300,
                'nice'         => 0,
            ],
        ],

        // ── Local / development ────────────────────────────────────────
        'local' => [
            'supervisor-local' => [
                'connection'   => 'redis',
                'queue'        => ['default', 'notifications', 'high', 'low'],
                'balance'      => 'simple',
                'autoScaling'  => false,
                'minProcesses' => 1,
                'maxProcesses' => 3,
                'tries'        => 3,
                'timeout'      => 90,
                'nice'         => 0,
            ],
        ],

        // ── Staging ───────────────────────────────────────────────────
        'staging' => [
            'supervisor-staging' => [
                'connection'   => 'redis',
                'queue'        => ['default', 'notifications', 'high', 'low'],
                'balance'      => 'auto',
                'autoScaling'  => false,
                'minProcesses' => 1,
                'maxProcesses' => 5,
                'tries'        => 3,
                'timeout'      => 90,
                'nice'         => 0,
            ],
        ],
    ],

];
