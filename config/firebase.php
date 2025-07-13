<?php

// return [
//         'file' => config_path('firebase_credentials.json'),
// ];


return [
    'api_key' => env('FIREBASE_API_KEY'),
    'auth_domain' => env('FIREBASE_AUTH_DOMAIN'),
    'project_id' => env('FIREBASE_PROJECT_ID'),
    'storage_bucket' => env('FIREBASE_STORAGE_BUCKET'),
    'app_id' => env('FIREBASE_APP_ID'),
    'measurement_id' => env('FIREBASE_MEASUREMENT_ID'),
    'vapid_key' => env('FIREBASE_VAPID_KEY'),

    'credentials' => [
        'file' => env('GOOGLE_APPLICATION_CREDENTIALS'),
    ],
];
