<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Table Configuration
    |--------------------------------------------------------------------------
    |
    | This values are used as default for all tables generated with
    | Livewire Table Kit. You can override these values per table.
    |
    */

    'pagination' => [
        'default_per_page' => 15,
        'per_page_options' => [15, 30, 50, 100],
        'show_per_page_selector' => true,
    ],

    'sorting' => [
        'default_field' => 'id',
        'default_direction' => 'desc',
        'persist_sort_in_session' => true,
    ],

    'search' => [
        'enabled' => true,
        'debounce' => 300,
        'min_characters' => 2,
    ],

    'export' => [
        'csv_enabled' => true,
        'xlsx_enabled' => true,
        'pdf_enabled' => true,
        'max_export_rows' => 10000,
    ],

    'appearance' => [
        'show_empty_state' => true,
        'show_column_toggles' => true,
        'striped_rows' => true,
        'hover_rows' => true,
    ],

];