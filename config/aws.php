<?php

return [
    'access_key_id' => env('AWS_ACCESS_KEY_ID'),
    'secret_access_key' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    'bucket' => env('AWS_BUCKET'),
    'textract' => [
        'region' => env('AWS_TEXTRACT_REGION', env('AWS_DEFAULT_REGION', 'us-east-1')),
    ],
];