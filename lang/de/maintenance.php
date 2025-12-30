<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Update Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Wartung',
    'description' => 'Auf dieser Seite finden Sie alle notwendigen Funktionen für den reibungslosen Betrieb Ihrer Lychee Installation.',
    'cleaning' => [
        'title' => 'Säubern %s',
        'result' => '%s gelöscht.',
        'description' => 'Lösche den gesamten Inhalt aus <span class="font-mono">%s</span>',
        'button' => 'Bereinigen',
    ],
    'duplicate-finder' => [
        'title' => 'Duplikate',
        'description' => 'Dieses Modul zählt potenzielle Duplikate von Bildern.',
        'duplicates-all' => 'Duplikate über alle Alben',
        'duplicates-title' => 'Titel-Duplikate pro Album',
        'duplicates-per-album' => 'Duplikate pro Album',
        'show' => 'Duplikate anzeigen',
    ],
    'fix-jobs' => [
        'title' => 'Korrigieren des Auftragsverlaufs',
        'description' => 'Markiere Jobs mit dem Status <span class="text-ready-400">%s</span> oder <span class="text-primary-500">%s</span> als <span class="text-danger-700">%s</span>.',
        'button' => 'Repariere Job Historie',
    ],
    'gen-sizevariants' => [
        'title' => 'Fehlende %s',
        'description' => 'Es wurden %d %s gefunden, welche noch angelegt werden können.',
        'button' => 'Anlegen!',
        'success' => 'Erfolgreich angelegt. %d %s.',
    ],
    'fill-filesize-sizevariants' => [
        'title' => 'Fehlende Dateigrößen',
        'description' => 'Es wurden %d kleine Varianten ohne Dateigröße gefunden.',
        'button' => 'Daten sammeln!',
        'success' => 'Die Daten für %d kleine Varianten wurden erfolgreich verarbeitet.',
    ],
    'fix-tree' => [
        'title' => 'Baumstruktur Statistik',
        'Oddness' => 'Ungewöhnlich',
        'Duplicates' => 'Duplikate',
        'Wrong parents' => 'Falsche Oberkategorie',
        'Missing parents' => 'Fehlende Oberkategorie',
        'button' => 'Baumstruktur reparieren',
    ],
    'optimize' => [
        'title' => 'Datenbank optimieren',
        'description' => 'Wenn die Performance Ihrer Installation nachlässt, könnte dies an fehlenden Datenbankindizes liegen.',
        'button' => 'Datenbank optimieren',
    ],
    'update' => [
        'title' => 'Updates',
        'check-button' => 'Auf Updates prüfen',
        'update-button' => 'Update',
        'no-pending-updates' => 'Keine Updates verfügbar.',
    ],
    'missing-palettes' => [
        'title' => 'Fehlende Paletten',
        'description' => '%d fehlende Paletten gefunden.',
        'button' => 'Fehlendes erstellen',
    ],
    'statistics-check' => [
        'title' => 'Integritätsprüfung der Statistik',
        'missing_photos' => '%d Fotostatistiken fehlen.',
        'missing_albums' => '%d Albumstatistiken fehlen.',
        'button' => 'Fehlendes erstellen',
    ],
    'flush-cache' => [
        'title' => 'Cache leeren',
        'description' => 'Leeren Sie den Cache jedes Benutzers, um Ungültigkeitsprobleme zu lösen.',
        'button' => 'Leeren',
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
];
