<?php

return [
    /*
     * Максимальна глибина рекурсії для перевірки циклів у ієрархії тенантів.
     */
    'max_hierarchy_depth' => env('TENANT_MAX_DEPTH', 10),
];
