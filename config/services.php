<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'groq' => [
        'key'   => env('GROQ_API_KEY'),
        'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
    ],

    // Zavu unified messaging API
    'zavu' => [
        'api_key'   => env('ZAVU_API_KEY', ''),
        'base_url'  => env('ZAVU_BASE_URL', 'https://api.zavu.dev/v1'),
        'sender_id' => env('ZAVU_SENDER_ID', ''),
    ],

    // Nepal SMS (Sparrow)
    'sms' => [
        'provider'      => env('SMS_PROVIDER', 'sparrow'),
        'sparrow_token' => env('SPARROW_SMS_TOKEN', ''),
        'sparrow_from'  => env('SPARROW_SMS_FROM', 'NSH'),
    ],

    // Nepal payment gateways
    'esewa' => [
        'secret_key'  => env('ESEWA_SECRET_KEY', ''),
        'success_url' => env('ESEWA_SUCCESS_URL', env('APP_URL') . '/finance/esewa/success'),
        'failure_url' => env('ESEWA_FAILURE_URL', env('APP_URL') . '/finance/esewa/failure'),
    ],
    'khalti' => [
        'secret_key'  => env('KHALTI_SECRET_KEY', ''),
        'return_url'  => env('KHALTI_RETURN_URL',  env('APP_URL') . '/finance/khalti/callback'),
        'website_url' => env('KHALTI_WEBSITE_URL', env('APP_URL', 'https://nshlms.com')),
    ],
    'payment_gateway' => [
        'default' => env('PAYMENT_GATEWAY_DEFAULT', 'esewa'),
    ],

    // -------------------------------------------------------------------------
    // Firebase Cloud Messaging (FCM v1)
    // -------------------------------------------------------------------------
    // Setup:
    //   1. Firebase Console -> Project Settings -> Cloud Messaging
    //      Sender ID               -> FCM_SENDER_ID
    //   2. Cloud Messaging -> Web configuration -> Generate key pair
    //      Public VAPID key        -> FCM_VAPID_PUBLIC_KEY
    //   3. Project Settings -> General -> Web app -> firebaseConfig
    //      apiKey                  -> FIREBASE_API_KEY
    //      authDomain              -> FIREBASE_AUTH_DOMAIN
    //      projectId               -> FCM_PROJECT_ID
    //      storageBucket           -> FIREBASE_STORAGE_BUCKET
    //      messagingSenderId       -> FCM_SENDER_ID
    //      appId                   -> FIREBASE_APP_ID
    //      measurementId           -> FIREBASE_MEASUREMENT_ID
    //   4. Service account key at storage/app/firebase-service-account.json
    //      (replaces legacy server key — FCM v1 API uses OAuth2)
    // -------------------------------------------------------------------------
    'fcm' => [
        'project_id'         => env('FCM_PROJECT_ID',            'nepalschoolhub'),
        'sender_id'          => env('FCM_SENDER_ID',             '505670317874'),
        'vapid_key'          => env('FCM_VAPID_PUBLIC_KEY',      ''),
        'api_key'            => env('FIREBASE_API_KEY',          ''),
        'auth_domain'        => env('FIREBASE_AUTH_DOMAIN',      ''),
        'storage_bucket'     => env('FIREBASE_STORAGE_BUCKET',   ''),
        'app_id'             => env('FIREBASE_APP_ID',           ''),
        'measurement_id'     => env('FIREBASE_MEASUREMENT_ID',   ''),
        'service_account'    => env('FCM_SERVICE_ACCOUNT_PATH',  'firebase-service-account.json'),
    ],

    // Push provider selector
    'push' => [
        'provider' => env('PUSH_PROVIDER', 'fcm'),
    ],

    // OneSignal (alternative push provider)
    'onesignal' => [
        'app_id'   => env('ONESIGNAL_APP_ID', ''),
        'rest_key' => env('ONESIGNAL_REST_KEY', ''),
    ],

    // -------------------------------------------------------------------------
    // Google reCAPTCHA v3 / reCAPTCHA Enterprise
    // -------------------------------------------------------------------------
    // Project:  nepalschoolhub | Domain: nshlms.com
    // Console:  https://console.cloud.google.com/security/recaptcha
    //
    // site_key   -> public, safe in HTML/JS
    // secret_key -> server-side verification only, keep SECRET
    //
    // The same site key is used for Firebase App Check.
    // -------------------------------------------------------------------------
    'recaptcha' => [
        'site_key'           => env('RECAPTCHA_SITE_KEY',          ''),
        'secret_key'         => env('RECAPTCHA_SECRET_KEY',        ''),
        'app_check_site_key' => env('FIREBASE_APP_CHECK_SITE_KEY', env('RECAPTCHA_SITE_KEY', '')),
    ],

];
