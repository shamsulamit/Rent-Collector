<?php

return [
    'name' => 'Landlord Ledger',

    'backup' => [
        'encrypt' => env('BACKUP_ENCRYPT', true),
        'encryption_key' => env('BACKUP_ENCRYPTION_KEY'),
        'google_credentials' => env('GOOGLE_DRIVE_CREDENTIALS'),
        'google_folder_id' => env('GOOGLE_DRIVE_FOLDER_ID'),
        'retention' => (int) env('BACKUP_RETENTION', 10),
    ],

    'whatsapp' => [
        'default_locale' => env('WHATSAPP_DEFAULT_LOCALE', 'en'),
    ],

    'utilities' => [
        'electricity_providers' => [
            'DESCO', 'DPDC', 'Palli Bidyut', 'NESCO', 'WZPDCL', 'BPDB', 'Custom',
        ],
        'default_slabs' => [
            ['min' => 0, 'max' => 75, 'rate' => 5.00],
            ['min' => 76, 'max' => 200, 'rate' => 6.50],
            ['min' => 201, 'max' => 300, 'rate' => 7.80],
            ['min' => 301, 'max' => 400, 'rate' => 9.00],
            ['min' => 401, 'max' => null, 'rate' => 10.50],
        ],
    ],

    'expense_categories' => \App\Models\Expense::CATEGORIES,
];
