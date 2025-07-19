<?php

return [

    // Максимальна глибина рекурсії для перевірки циклів у ієрархії тенантів.
    'max_hierarchy_depth' => env('TENANT_MAX_DEPTH', 10),

    // ID «безкоштовного» тенанта
    'free_tenant_id' => env('TENANT_FREE_ID', 7),

    // Корінь SaaS-домену для піддоменів
    'saas_root' => env('APP_SAAS_ROOT', 'example.com'),
];
