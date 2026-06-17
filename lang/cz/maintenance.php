<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Update Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Údržba',
    'description' => 'Na této stránce najdete všechny kroky potřebné k tomu, aby vaše instalace Lychee fungovala hladce a bez problémů.',
    'cleaning' => [
        'title' => 'Vyčištění %s',
        'result' => '%s smazáno.',
        'description' => 'Odebrat veškerý obsah z <span class="font-mono">%s</span>',
        'button' => 'Vyčistit',
    ],
    'duplicate-finder' => [
        'title' => 'Duplikáty',
        'description' => 'Tento modul počítá potenciální duplikáty mezi obrázky.',
        'duplicates-all' => 'Duplikáty ve všech albech',
        'duplicates-title' => 'Duplikáty názvů v albu',
        'duplicates-per-album' => 'Duplikáty v albu',
        'show' => 'Zobrazit duplikáty',
        'load' => 'Načíst počty',
    ],
    'fix-jobs' => [
        'title' => 'Oprava historie úloh',
        'description' => 'Označit úlohy se stavem <span class="text-ready-400">%s</span> nebo <span class="text-primary-500">%s</span> jako <span class="text-danger-700">%s</span>.',
        'button' => 'Opravit historii úloh',
    ],
    'gen-sizevariants' => [
        'title' => 'Chybí %s',
        'description' => 'Nalezeno %d %s, které by mohly být vygenerovány.',
        'button' => 'Vygenerovat!',
        'success' => 'Úspěšně vygenerováno %d %s.',
    ],
    'fill-filesize-sizevariants' => [
        'title' => 'Chybějící velikosti souborů',
        'description' => 'Nalezeno %d malých variant bez velikosti souboru.',
        'button' => 'Načíst data!',
        'success' => 'Úspěšně vypočítány velikosti %d malých variant.',
    ],
    'fix-tree' => [
        'title' => 'Statistiky stromu',
        'Oddness' => 'Oddness',
        'Duplicates' => 'Duplikáty',
        'Wrong parents' => 'Nesprávní rodiče',
        'Missing parents' => 'Chybějící rodiče',
        'button' => 'Opravit strom',
    ],
    'optimize' => [
        'title' => 'Optimalizovat databázi',
        'description' => 'Pokud zaznamenáte zpomalení vaší instalace, může to být způsobeno tím, že vaše databáze nemá všechny potřebné indexy.',
        'button' => 'Optimalizovat databázi',
    ],
    'update' => [
        'title' => 'Aktualizace',
        'check-button' => 'Zkontrolovat aktualizace',
        'update-button' => 'Aktualizovat',
        'no-pending-updates' => 'Žádné čekající aktualizace.',
    ],
    'missing-palettes' => [
        'title' => 'Chybějící palety',
        'description' => 'Nalezeno %d chybějících palet.',
        'button' => 'Vytvořit chybějící',
    ],
    'statistics-check' => [
        'title' => 'Kontrola integrity statistik',
        'missing_photos' => 'Chybí statistiky pro %d fotografií.',
        'missing_albums' => 'Chybí statistiky pro %d alb.',
        'button' => 'Vytvořit chybějící',
    ],
    'flush-cache' => [
        'title' => 'Vyprázdnit mezipaměť',
        'description' => 'Vyprázdněte mezipaměť každého uživatele, abyste vyřešili problémy s neplatností.',
        'button' => 'Vyprázdnit',
    ],
    'old-orders' => [
        'title' => 'Staré objednávky',
        'description' => 'Nalezeno %d starých objednávek.<br/><br/>Stará objednávka je starší než 14 dní, nemá přiřazeného uživatele a buď je stále v čekání na platbu, nebo neobsahuje žádné položky.',
        'button' => 'Odstranit staré objednávky',
    ],
    'fulfill-orders' => [
        'title' => 'Objednávky k vyřízení',
        'description' => 'Nalezeno %d objednávek s obsahem, který nebyl zpřístupněn.<br/><br/>Klikněte na tlačítko a přiřaďte obsah, pokud je to možné.',
        'button' => 'Vyřídit objednávky',
    ],
    'fulfill-precompute' => [
        'title' => 'Předpočítaná pole alb',
        'description' => 'Bylo nalezeno %d alb s chybějícími předpočítanými poli. <br/><br/>Odpovídá spuštění: php artisan lychee:recompute-album-fields',
        'button' => 'Vypočítat pole',
    ],
    'flush-queue' => [
        'title' => 'Vyčistit frontu',
        'description' => 'Ve frontě bylo nalezeno %d čekajících úloh. <br/><br/>UPOZORNĚNÍ: Vyčištění fronty trvale odstraní všechny čekající úlohy. Tuto akci nelze vrátit zpět.',
        'button' => 'Vyčistit frontu',
    ],
    'backfill-album-sizes' => [
        'title' => 'Statistiky velikosti alb',
        'description' => 'Nalezeno %d alb bez statistik velikosti.<br/><br/>Odpovídá spuštění: php artisan lychee:recompute-album-sizes',
        'button' => 'Přepočítat velikosti',
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
        'readd' => 'Re-add face',
        'load_error' => 'Failed to load faces.',
        'dismissed' => 'Face dismissed.',
        'readded' => 'Face re-added.',
        'dismiss_error' => 'Failed to dismiss face.',
        'readd_error' => 'Failed to re-add face.',
        'batch_dismiss' => 'Dismiss selected',
        'batch_dismissed' => ':count face(s) dismissed.',
        'batch_dismiss_error' => 'Failed to dismiss selected faces.',
        'batch_reactivate' => 'Reactivate selected',
        'batch_reactivated' => ':count face(s) reactivated.',
        'batch_reactivate_error' => 'Failed to reactivate selected faces.',
        'show_dismissed' => 'Show dismissed',
        'show_active' => 'Show active',
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
