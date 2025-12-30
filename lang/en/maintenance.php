<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Maintenance Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Maintenance',
    'description' => 'On this page you will find, all the required actions to keep your Lychee installation running smooth and nicely.',
    'cleaning' => [
        'title' => 'Cleaning %s',
        'result' => '%s deleted.',
        'description' => 'Remove all contents from <span class="font-mono">%s</span>',
        'button' => 'Clean',
    ],
    'duplicate-finder' => [
        'title' => 'Duplicates',
        'description' => 'This module counts potential duplicates betwen pictures.',
        'duplicates-all' => 'Duplicates over all albums',
        'duplicates-title' => 'Title duplicates per album',
        'duplicates-per-album' => 'Duplicates per album',
        'show' => 'Show duplicates',
    ],
    'fix-jobs' => [
        'title' => 'Fixing Jobs History',
        'description' => 'Mark jobs with status <span class="text-ready-400">%s</span> or <span class="text-primary-500">%s</span> as <span class="text-danger-700">%s</span>.',
        'button' => 'Fix job history',
    ],
    'gen-sizevariants' => [
        'title' => 'Missing %s',
        'description' => 'Found %d %s that could be generated.',
        'button' => 'Generate!',
        'success' => 'Successfully generated %d %s.',
    ],
    'fill-filesize-sizevariants' => [
        'title' => 'File sizes missing',
        'description' => 'Found %d small variants without file size.',
        'button' => 'Fetch data!',
        'success' => 'Successfully computed sizes of %d small variants.',
    ],
    'fix-tree' => [
        'title' => 'Tree statistics',
        'Oddness' => 'Oddness',
        'Duplicates' => 'Duplicates',
        'Wrong parents' => 'Wrong parents',
        'Missing parents' => 'Missing parents',
        'button' => 'Fix tree',
    ],
    'optimize' => [
        'title' => 'Optimize Database',
        'description' => 'If you notice slowdown in your installation, it may be because your database does not have all its needed index.',
        'button' => 'Optimize Database',
    ],
    'update' => [
        'title' => 'Updates',
        'check-button' => 'Check for updates',
        'update-button' => 'Update',
        'no-pending-updates' => 'No pending update.',
    ],
    'missing-palettes' => [
        'title' => 'Missing Palettes',
        'description' => 'Found %d missing palettes.',
        'button' => 'Create missing',
    ],
    'statistics-check' => [
        'title' => 'Statistics integrity Check',
        'missing_photos' => '%d photo statistics missing.',
        'missing_albums' => '%d album statistics missing.',
        'button' => 'Create missing',
    ],
    'flush-cache' => [
        'title' => 'Flush Cache',
        'description' => 'Flush the cache of every user to solve invalidation problems.',
        'button' => 'Flush',
    ],
    'old-orders' => [
        'title' => 'Old Orders',
        'description' => 'Found %d old orders.<br/><br/>An old order is older than 14 days, that have no associated user and are either still pending payment or have no items in them.',
        'button' => 'Delete old orders',
    ],
    'fulfill-orders' => [
        'title' => 'Orders to fulfill',
        'description' => 'Found %d orders with content that has not been made available.<br/><br/>Click on the button to assign content when possible.',
        'button' => 'Fulfill orders',
    ],
    'fulfill-precompute' => [
        'title' => 'Album Precomputed Fields',
        'description' => 'Found %d albums with missing precomputed fields.<br/><br/>Equivalent to running: php artisan lychee:backfill-album-fields',
        'button' => 'Compute fields',
    ],
];
