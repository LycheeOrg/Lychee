<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Update Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Onderhoud',
    'description' => 'Op deze pagina vindt u alle benodigde acties om uw Lychee-installatie soepel en netjes te laten werken.',
    'cleaning' => [
        'title' => 'Opschonen %s',
        'result' => '%s verwijderd.',
        'description' => 'Verwijder alle inhoud van <span class="font-mono">%s</span>',
        'button' => 'Opschonen',
    ],
    'duplicate-finder' => [
        'title' => 'Duplicaten',
        'description' => 'Deze module telt mogelijke duplicaten tussen foto’s.',
        'duplicates-all' => 'Duplicaten over alle albums',
        'duplicates-title' => 'Titelduplicaten per album',
        'duplicates-per-album' => 'Duplicaten per album',
        'show' => 'Toon duplicaten',
        'load' => 'Load counts',
    ],
    'fix-jobs' => [
        'title' => 'Taakgeschiedenis herstellen',
        'description' => 'Markeer taken met status <span class="text-ready-400">%s</span> of <span class="text-primary-500">%s</span> als <span class="text-danger-700">%s</span>.',
        'button' => 'Herstel taakgeschiedenis',
    ],
    'gen-sizevariants' => [
        'title' => 'Ontbrekende %s',
        'description' => '%d %s gevonden die gegenereerd kunnen worden.',
        'button' => 'Genereer!',
        'success' => 'Succesvol %d %s gegenereerd.',
    ],
    'fill-filesize-sizevariants' => [
        'title' => 'Bestandsgroottes ontbreken',
        'description' => '%d kleine varianten zonder bestandsgrootte gevonden.',
        'button' => 'Gegevens ophalen!',
        'success' => 'Succesvol de groottes van %d kleine varianten berekend.',
    ],
    'fix-tree' => [
        'title' => 'Boomstatistieken',
        'Oddness' => 'Onregelmatigheden',
        'Duplicates' => 'Duplicaten',
        'Wrong parents' => 'Verkeerde ouders',
        'Missing parents' => 'Ontbrekende ouders',
        'button' => 'Herstel boom',
    ],
    'optimize' => [
        'title' => 'Database optimaliseren',
        'description' => 'Als u vertragingen in uw installatie opmerkt, kan dit komen doordat uw database niet alle benodigde indexen heeft.',
        'button' => 'Optimaliseer database',
    ],
    'update' => [
        'title' => 'Updates',
        'check-button' => 'Controleer op updates',
        'update-button' => 'Bijwerken',
        'no-pending-updates' => 'Geen updates in behandeling.',
    ],
    'missing-palettes' => [
        'title' => 'Ontbrekende paletten',
        'description' => '%d ontbrekende paletten gevonden.',
        'button' => 'Ontbrekende aanmaken',
    ],
    'statistics-check' => [
        'title' => 'Controle op statistische integriteit',
        'missing_photos' => '%d fotostatistieken ontbreken.',
        'missing_albums' => '%d albumstatistieken ontbreken.',
        'button' => 'Maak ontbrekende aan',
    ],
    'flush-cache' => [
        'title' => 'Cache legen',
        'description' => 'Leeg de cache van elke gebruiker om invalidatieproblemen op te lossen.',
        'button' => 'Leeg cache',
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
        'description' => 'Found %d albums with missing precomputed fields.<br/><br/>Equivalent to running: php artisan lychee:recompute-album-fields',
        'button' => 'Compute fields',
    ],
    'flush-queue' => [
        'title' => 'Flush Queue',
        'description' => 'Found %d pending jobs in the queue.<br/><br/>CAUTION: Clearing the queue will permanently delete all pending jobs. This cannot be undone.',
        'button' => 'Clear queue',
    ],
    'backfill-album-sizes' => [
        'title' => 'Album Size Statistics',
        'description' => 'Found %d albums without size statistics.<br/><br/>Equivalent to running: php artisan lychee:recompute-album-sizes',
        'button' => 'Compute sizes',
    ],

    'face_quality' => [
        'title' => 'Face Quality Review',
        'description' => 'Review face detections by quality score and dismiss low-quality or erroneous faces.',
        'sort_by' => 'Sort by:',
        'sort_confidence' => 'Confidence',
        'sort_blur' => 'Blur (Laplacian)',
        'no_faces' => 'No qualifying faces. Everything looks good!',
        'col_face' => 'Face',
        'col_person' => 'Person',
        'col_cluster' => 'Cluster',
        'col_confidence' => 'Confidence',
        'col_blur' => 'Blur Score',
        'col_actions' => 'Actions',
        'unassigned' => 'Unassigned',
        'dismiss' => 'Dismiss face',
        'load_error' => 'Failed to load faces.',
        'dismissed' => 'Face dismissed.',
        'dismiss_error' => 'Failed to dismiss face.',
        'batch_dismiss' => 'Dismiss selected',
        'batch_dismissed' => ':count face(s) dismissed.',
        'batch_dismiss_error' => 'Failed to dismiss selected faces.',
        'select_all' => 'Select all',
        'deselect_all' => 'Deselect all',
        'selected_count' => ':count selected',
    ],
    'bulk-scan-faces' => [
        'description' => 'Found %d photos that have not yet been scanned for facial recognition.<br/><br/>Requires the AI Vision service to be running.',
    ],
    'run-clustering' => [
        'description' => 'Trigger face clustering in the AI Vision service. Groups detected faces by similarity so you can assign them to people.',
        'success' => 'Clustering started successfully.',
    ],
    'destroy-dismissed-faces' => [
        'title' => 'Destroy Dismissed Faces',
        'description' => 'Found %d dismissed faces. Destroying them will permanently delete their crop files and embeddings.',
        'action' => 'Destroy All',
        'success' => 'Dismissed faces destroyed successfully.',
    ],
    'sync-face-embeddings' => [
        'title' => 'Sync Face Embeddings',
        'description' => 'Face count mismatch detected (%d difference). Syncing will pull latest face data from AI Vision service to Lychee.',
        'action' => 'Sync Now',
        'success' => 'Face embeddings synchronized successfully.',
    ],
    'reset-face-scan-status' => [
        'title' => 'Reset Face Scan Status',
        'description' => 'Found %d photos with a stuck-pending or failed face scan status. Resetting them will allow them to be re-scanned.',
        'action' => 'Reset All',
        'success' => 'Face scan statuses reset successfully.',
    ],

    ];
