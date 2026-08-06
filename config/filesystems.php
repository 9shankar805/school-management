<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    | Set FILESYSTEM_DRIVER=s3 in .env to switch all uploads to S3/cloud.
    | Set to "public" to serve files from storage/app/public (local dev).
    */
    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Cloud Disk
    |--------------------------------------------------------------------------
    | Used by Storage::cloud() — independent of the default disk so you can
    | write locally but still push backups / exports to S3.
    */
    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    | Module 23 additions:
    |   - s3-private   — for sensitive docs (contracts, medical records, IDs)
    |   - s3-public    — for public assets (avatars, published study notes)
    |   - backups      — dedicated bucket for spatie/laravel-backup
    |   - digitalocean — DigitalOcean Spaces (S3-compatible)
    |   - minio        — local S3-compatible storage for development/CI
    */
    'disks' => [

        // ── Local (default for development) ───────────────────────────
        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app'),
        ],

        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'url'        => env('APP_URL') . '/storage',
            'visibility' => 'public',
        ],

        // ── AWS S3 — general uploads (private by default) ─────────────
        's3' => [
            'driver'                  => 's3',
            'key'                     => env('AWS_ACCESS_KEY_ID'),
            'secret'                  => env('AWS_SECRET_ACCESS_KEY'),
            'region'                  => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'bucket'                  => env('AWS_BUCKET'),
            'url'                     => env('AWS_URL'),
            'endpoint'                => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'visibility'              => 'private',
            // Signed URL expiry for private files (seconds)
            'temporary_url_expiry'    => env('AWS_SIGNED_URL_EXPIRY', 900),
        ],

        // ── AWS S3 — sensitive / private documents ─────────────────────
        // Student IDs, medical records, contracts — never publicly accessible.
        's3-private' => [
            'driver'                  => 's3',
            'key'                     => env('AWS_ACCESS_KEY_ID'),
            'secret'                  => env('AWS_SECRET_ACCESS_KEY'),
            'region'                  => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'bucket'                  => env('AWS_PRIVATE_BUCKET', env('AWS_BUCKET')),
            'url'                     => env('AWS_URL'),
            'endpoint'                => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'visibility'              => 'private',
            'temporary_url_expiry'    => env('AWS_SIGNED_URL_EXPIRY', 900),
            // Store under a dedicated prefix to make bucket policies easy
            'root'                    => 'private',
        ],

        // ── AWS S3 — public assets (avatars, published notes, banners) ──
        's3-public' => [
            'driver'                  => 's3',
            'key'                     => env('AWS_ACCESS_KEY_ID'),
            'secret'                  => env('AWS_SECRET_ACCESS_KEY'),
            'region'                  => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'bucket'                  => env('AWS_PUBLIC_BUCKET', env('AWS_BUCKET')),
            'url'                     => env('AWS_URL'),
            'endpoint'                => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'visibility'              => 'public',
            'root'                    => 'public',
        ],

        // ── S3 bucket dedicated to spatie/laravel-backup ──────────────
        'backups' => [
            'driver'                  => 's3',
            'key'                     => env('AWS_ACCESS_KEY_ID'),
            'secret'                  => env('AWS_SECRET_ACCESS_KEY'),
            'region'                  => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'bucket'                  => env('AWS_BACKUP_BUCKET', env('AWS_BUCKET')),
            'url'                     => env('AWS_URL'),
            'endpoint'                => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'visibility'              => 'private',
            'root'                    => 'backups',
        ],

        // ── DigitalOcean Spaces (S3-compatible) ────────────────────────
        // Set DO_SPACES_ENDPOINT=https://<region>.digitaloceanspaces.com
        'digitalocean' => [
            'driver'                  => 's3',
            'key'                     => env('DO_SPACES_KEY'),
            'secret'                  => env('DO_SPACES_SECRET'),
            'region'                  => env('DO_SPACES_REGION', 'nyc3'),
            'bucket'                  => env('DO_SPACES_BUCKET'),
            'endpoint'                => env('DO_SPACES_ENDPOINT'),
            'use_path_style_endpoint' => false,
            'visibility'              => 'private',
        ],

        // ── MinIO (S3-compatible — use in local Docker dev / CI) ───────
        // docker run -p 9000:9000 -p 9001:9001 minio/minio server /data --console-address ":9001"
        'minio' => [
            'driver'                  => 's3',
            'key'                     => env('MINIO_KEY', 'minioadmin'),
            'secret'                  => env('MINIO_SECRET', 'minioadmin'),
            'region'                  => env('MINIO_REGION', 'us-east-1'),
            'bucket'                  => env('MINIO_BUCKET', 'school-erp'),
            'endpoint'                => env('MINIO_ENDPOINT', 'http://localhost:9000'),
            'use_path_style_endpoint' => true,  // required for MinIO
            'visibility'              => 'private',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    */
    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
