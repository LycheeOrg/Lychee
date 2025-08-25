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
];
