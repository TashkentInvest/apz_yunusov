<?php

// config/contract.php

return [
    // Base calculation amounts configuration
    'base_amounts' => [
        'default' => 412000, // Default base amount in UZS
        'currency' => 'UZS',
        'update_frequency' => 'yearly', // How often base amounts are updated
    ],

    // Coefficient limits
    'coefficient_limits' => [
        'min' => 0.50,
        'max' => 2.00,
    ],

    // Construction type coefficients (Kt)
    'construction_type_coefficients' => [
        'new_construction' => 1.0,
        'reconstruction_with_volume_change' => 1.0,
        'reconstruction_without_expertise' => 0.0,
        'reconstruction_without_volume_change' => 0.0,
    ],

    // Object type coefficients (Ko)
    'object_type_coefficients' => [
        'social_infrastructure_tourism' => 0.5,
        'state_investment_projects' => 0.5,
        'production_enterprise_facilities' => 0.5,
        'warehouses_low_height' => 0.5,
        'other_objects' => 1.0,
    ],

    // Territorial zone coefficients (Kz)
    'territorial_zone_coefficients' => [
        'zone_1' => 1.40,
        'zone_2' => 1.25,
        'zone_3' => 1.00,
        'zone_4' => 0.75,
        'zone_5' => 0.50,
    ],

    // Location coefficients (Kj)
    'location_coefficients' => [
        'metro_radius_200m_outside' => 0.6,
        'other_locations' => 1.0,
    ],

    // Payment configuration
    'payment' => [
        'default_initial_percent' => 20,
        'min_initial_percent' => 0,
        'max_initial_percent' => 100,
        'default_construction_period_years' => 2,
        'min_construction_period_years' => 1,
        'max_construction_period_years' => 10,
        'quarters_per_year' => 4,
    ],

    // Map configuration
    'map' => [
        'default_center' => [
            'lat' => 41.2995,
            'lng' => 69.2401,
        ],
        'default_zoom' => 10,
        'zone_colors' => [
            'ЗОНА-1' => '#ff0000',
            'ЗОНА-2' => '#ff8800',
            'ЗОНА-3' => '#ffff00',
            'ЗОНА-4' => '#88ff00',
            'ЗОНА-5' => '#00ff00',
            'default' => '#888888',
        ],
        'kml_file_path' => 'zona.kml',
    ],

    // Validation rules
    'validation' => [
        'contract_number' => [
            'prefix' => 'АПЗ-',
            'max_length' => 50,
        ],
        'volumes' => [
            'min_construction_volume' => 0.01,
            'max_volume' => 999999.99,
            'decimal_places' => 2,
        ],
        'amounts' => [
            'max_amount' => 999999999999.99,
            'decimal_places' => 2,
        ],
    ],

    // UI Configuration
    'ui' => [
        'pagination' => [
            'contracts_per_page' => 20,
            'subjects_per_page' => 20,
            'objects_per_page' => 20,
        ],
        'date_format' => 'd.m.Y',
        'datetime_format' => 'd.m.Y H:i',
        'number_format' => [
            'decimals' => 2,
            'decimal_separator' => '.',
            'thousands_separator' => ' ',
        ],
    ],

    // Status configuration
    'statuses' => [
        'default' => [
            'draft' => 'Черновик',
            'active' => 'Активный',
            'completed' => 'Завершен',
            'cancelled' => 'Отменен',
        ],
        'colors' => [
            'draft' => '#6b7280',
            'active' => '#059669',
            'completed' => '#2563eb',
            'cancelled' => '#dc2626',
        ],
    ],

    // Document types for physical persons
    'document_types' => [
        'passport' => 'Паспорт',
        'id_card' => 'ID карта',
        'birth_certificate' => 'Свидетельство о рождении',
    ],

    // Countries
    'countries' => [
        'UZ' => 'Узбекистан',
        'RU' => 'Россия',
        'KZ' => 'Казахстан',
        'KG' => 'Кыргызстан',
        'TJ' => 'Таджикистан',
        'TM' => 'Туркменистан',
    ],

    // Formula configuration
    'formula' => [
        'description' => 'Ti = Bh * ((Hb + Hyu) - (Ha + Ht + Hu)) * Kt * Ko * Kz * Kj',
        'variables' => [
            'Ti' => 'Сумма к доплате за создание инженерно-коммуникационной и транспортной инфраструктуры',
            'Bh' => 'Базовая расчетная величина',
            'Hb' => 'Общий объем здания',
            'Hyu' => 'Объем здания выше разрешенного количества этажей',
            'Ha' => 'Общий объем части здания для автостоянки',
            'Ht' => 'Общий объем технических этажей, сооружений и помещений здания',
            'Hu' => 'Общий объем части жилого здания общего пользования',
            'Kt' => 'Коэффициент типа строительства',
            'Ko' => 'Коэффициент типа объекта',
            'Kz' => 'Коэффициент территориальных зон',
            'Kj' => 'Коэффициент расположения объекта',
        ],
    ],

    // File upload configuration
    'uploads' => [
        'max_file_size' => 10240, // KB
        'allowed_extensions' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
        'paths' => [
            'contracts' => 'uploads/contracts',
            'subjects' => 'uploads/subjects',
            'objects' => 'uploads/objects',
        ],
    ],

    // Backup and logging
    'logging' => [
        'log_level' => 'info',
        'log_channels' => ['single', 'database'],
        'retention_days' => 90,
    ],

    // Performance settings
    'performance' => [
        'cache_ttl' => 3600, // seconds
        'enable_query_caching' => true,
        'max_search_results' => 1000,
    ],

    // Integration settings
    'integration' => [
        'external_apis' => [
            'enabled' => false,
            'timeout' => 30, // seconds
        ],
        'export_formats' => ['excel', 'pdf', 'csv'],
    ],
];
