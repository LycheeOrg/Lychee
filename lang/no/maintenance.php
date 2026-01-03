<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Update Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Vedlikehold',
    'description' => 'På denne siden finner du alle nødvendige handlinger for å holde Lychee-installasjonen din i gang knirkefritt.',
    'cleaning' => [
        'title' => 'Cleaning %s',
        'result' => '%s ble slettet.',
        'description' => 'Fjern alt innhold fra <span class="font-mono">%s</span>',
        'button' => 'Clean',
    ],
    'duplicate-finder' => [
        'title' => 'Duplikater',
        'description' => 'This module counts potential duplicates betwen pictures.',
        'duplicates-all' => 'Duplicates over all albums',
        'duplicates-title' => 'Title duplicates per album',
        'duplicates-per-album' => 'Duplicates per album',
        'show' => 'Vis duplikater',
        'load' => 'Load counts',
    ],
    'fix-jobs' => [
        'title' => 'Fixing Jobs History',
        'description' => 'Mark jobs with status <span class="text-ready-400">%s</span> or <span class="text-primary-500">%s</span> as <span class="text-danger-700">%s</span>.',
        'button' => 'Fix job history',
    ],
    'gen-sizevariants' => [
        'title' => 'Missing %s',
        'description' => 'Found %d %s that could be generated.',
        'button' => 'Generere!',
        'success' => 'Successfully generated %d %s.',
    ],
    'fill-filesize-sizevariants' => [
        'title' => 'File sizes missing',
        'description' => 'Found %d small variants without file size.',
        'button' => 'Hent data!',
        'success' => 'Successfully computed sizes of %d small variants.',
    ],
    'fix-tree' => [
        'title' => 'Tree statistics',
        'Oddness' => 'Oddness',
        'Duplicates' => 'Duplikater',
        'Wrong parents' => 'Wrong parents',
        'Missing parents' => 'Missing parents',
        'button' => 'Fix tree',
    ],
    'optimize' => [
        'title' => 'Optimaliser databasen',
        'description' => 'Hvis du merker at installasjonen er treg, kan det skyldes at databasen din ikke har all den nødvendige indeksen.',
        'button' => 'Optimaliser databasen',
    ],
    'update' => [
        'title' => 'Oppdateringer',
        'check-button' => 'Se etter oppdateringer',
        'update-button' => 'Oppdater',
        'no-pending-updates' => 'Ingen ventende oppdateringer.',
    ],
    'missing-palettes' => [
        'title' => 'Missing Palettes',
        'description' => 'Found %d missing palettes.',
        'button' => 'Create missing',
    ],
    'statistics-check' => [
        'title' => 'Statistikk integritetskontroll',
        'missing_photos' => '%d fotostatistikk mangler.',
        'missing_albums' => '%d albumstatistikk mangler.',
        'button' => 'Opprett manglende',
    ],
    'flush-cache' => [
        'title' => 'Flush Cache',
        'description' => 'Tøm hurtigbufferen til alle brukere for å løse ugyldighetsproblemer.',
        'button' => 'Tøm',
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
    'flush-queue' => [
        'title' => 'Flush Queue',
        'description' => 'Found %d pending jobs in the queue.<br/><br/>CAUTION: Clearing the queue will permanently delete all pending jobs. This cannot be undone.',
        'button' => 'Clear queue',
    ],
    'backfill-album-sizes' => [
        'title' => 'Album Size Statistics',
        'description' => 'Found %d albums without size statistics.<br/><br/>Equivalent to running: php artisan lychee:backfill-album-sizes',
        'button' => 'Compute sizes',
    ],
];
