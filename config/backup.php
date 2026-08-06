<?php

return [

    'backup' => [

        /*
        |--------------------------------------------------------------------------
        | Backup Name
        |--------------------------------------------------------------------------
        | The name of this application's backups. Used as the top-level folder
        | name inside the backup destination. Keep it slug-safe.
        */
        'name' => env('APP_NAME', 'school-erp'),

        'source' => [

            'files' => [

                /*
                | Files and directories to include in the backup.
                | Paths are relative to the project root.
                */
                'include' => [
                    base_path(),
                ],

                /*
                | Files / directories to exclude from the backup zip.
                | Always exclude vendor, node_modules, and sensitive .env files
                | from the archive — restore them via composer install + .env copy.
                */
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                    base_path('.git'),
                    storage_path('logs'),
                    storage_path('framework/cache'),
                    storage_path('framework/sessions'),
                    storage_path('framework/views'),
                    public_path('storage'),  // symlink — excluded to avoid duplication
                ],

                'followLinks' => false,

                'ignoreUnreadableDirs' => true,

                'relativePathForZipFile' => null,
            ],

            /*
            | Databases to include in the backup.
            | Uses the connections defined in config/database.php.
            | 'mysql' will produce a mysqldump .sql file inside the zip.
            */
            'databases' => [
                'mysql',
            ],
        ],

        'database_dump_compressor' => null,

        'database_dump_file_extension' => '',

        'destination' => [

            /*
            | Filename prefix for each backup archive.
            | Full filename: <filename_prefix>-<timestamp>.zip
            */
            'filename_prefix' => 'backup-',

            /*
            | Filesystem disks to write backups to.
            | 'backups' is the dedicated S3 bucket defined in config/filesystems.php.
            | 'local' is kept as a secondary copy for quick access.
            */
            'disks' => [
                'local',
                'backups',   // Module 23: S3 backup bucket
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Backup Encryption
        |--------------------------------------------------------------------------
        | Encrypt the backup zip. Set BACKUP_ARCHIVE_PASSWORD in .env.
        | Leave null to disable encryption (not recommended for production).
        */
        'password' => env('BACKUP_ARCHIVE_PASSWORD'),

        'encryption' => 'default',

        'tries' => 1,

        'retry_delay' => 0,
    ],

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    | Notify the team when a backup succeeds, fails, or is unhealthy.
    */
    'notifications' => [

        'notifications' => [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification::class          => ['mail'],
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification::class => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification::class        => ['mail'],
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification::class     => ['mail'],
            \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFoundNotification::class   => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessfulNotification::class    => [],
        ],

        'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,

        'mail' => [
            'to' => env('BACKUP_NOTIFY_EMAIL', env('MAIL_FROM_ADDRESS', 'backup@school.local')),
            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'noreply@school.local'),
                'name'    => env('MAIL_FROM_NAME', 'SchoolHub Backup'),
            ],
        ],

        'slack' => [
            'webhook_url' => env('BACKUP_SLACK_WEBHOOK_URL', ''),
            'channel'     => env('BACKUP_SLACK_CHANNEL', '#backups'),
            'username'    => 'SchoolHub Backup Bot',
            'icon'        => ':floppy_disk:',
        ],

        'discord' => [
            'webhook_url' => env('BACKUP_DISCORD_WEBHOOK_URL', ''),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitor the health of all backups
    |--------------------------------------------------------------------------
    */
    'monitor_backups' => [
        [
            'name'          => env('APP_NAME', 'school-erp'),
            'disks'         => ['local', 'backups'],
            // Alert if newest backup is older than 1 day
            'health_checks' => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class         => 1,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Cleanup Strategy
    |--------------------------------------------------------------------------
    | Keeps a rolling window of daily, weekly, monthly, and yearly backups.
    | This avoids unbounded S3 storage growth.
    */
    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,

        'default_strategy' => [
            'keep_all_backups_for_days'                    => 7,
            'keep_daily_backups_for_days'                  => 16,
            'keep_weekly_backups_for_weeks'                => 8,
            'keep_monthly_backups_for_months'              => 4,
            'keep_yearly_backups_for_years'                => 2,
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ],

        'tries' => 1,
        'retry_delay' => 0,
    ],

];
