<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Update Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Underhåll',
    'description' => 'På den här sidan hittar du alla åtgärder som behövs för att din Lychee-installation ska fungera smidigt.',
    'cleaning' => [
        'title' => 'Rensa %s',
        'result' => '%s raderades.',
        'description' => 'Ta bort allt innehåll från <span class="font-mono">%s</span>',
        'button' => 'Rensa',
    ],
    'duplicate-finder' => [
        'title' => 'Dubbletter',
        'description' => 'Den här modulen räknar möjliga dubbletter bland bilderna.',
        'duplicates-all' => 'Dubbletter i alla album',
        'duplicates-title' => 'Title duplicates per album',
        'duplicates-per-album' => 'Dubbletter per album',
        'show' => 'Visa dubbletter',
        'load' => 'Läs in antal',
    ],
    'fix-jobs' => [
        'title' => 'Reparera jobbhistorik',
        'description' => 'Markera jobb med status <span class="text-ready-400">%s</span> eller <span class="text-primary-500">%s</span> som <span class="text-danger-700">%s</span>.',
        'button' => 'Reparera jobbhistorik',
    ],
    'gen-sizevariants' => [
        'title' => 'Saknade %s',
        'description' => 'Hittade %d %s som kan genereras.',
        'button' => 'Generera!',
        'success' => 'Genererade %d %s.',
    ],
    'fill-filesize-sizevariants' => [
        'title' => 'Filstorlekar saknas',
        'description' => 'Hittade %d små varianter utan filstorlek.',
        'button' => 'Hämta data!',
        'success' => 'Beräknade storleken för %d små varianter.',
    ],
    'fix-tree' => [
        'title' => 'Trädstatistik',
        'Oddness' => 'Avvikelser',
        'Duplicates' => 'Dubbletter',
        'Wrong parents' => 'Fel föräldrar',
        'Missing parents' => 'Saknade föräldrar',
        'button' => 'Reparera träd',
    ],
    'optimize' => [
        'title' => 'Optimera databas',
        'description' => 'Om du märker att din installation blir långsam kan det bero på att databasen saknar nödvändiga index.',
        'button' => 'Optimera databas',
    ],
    'missing-palettes' => [
        'title' => 'Saknade paletter',
        'description' => 'Hittade %d saknade paletter.',
        'button' => 'Skapa saknade',
    ],
    'statistics-check' => [
        'title' => 'Integritetskontroll av statistik',
        'missing_photos' => '%d fotostatistikposter saknas.',
        'missing_albums' => '%d albumstatistikposter saknas.',
        'button' => 'Skapa saknade',
    ],
    'flush-cache' => [
        'title' => 'Töm cache',
        'description' => 'Töm cachen för alla användare för att lösa invalideringsproblem.',
        'button' => 'Töm',
    ],
    'old-orders' => [
        'title' => 'Gamla beställningar',
        'description' => 'Hittade %d gamla beställningar.<br/><br/>En gammal beställning är äldre än 14 dagar, saknar kopplad användare och väntar antingen fortfarande på betalning eller innehåller inga artiklar.',
        'button' => 'Radera gamla beställningar',
    ],
    'fulfill-orders' => [
        'title' => 'Beställningar att leverera',
        'description' => 'Hittade %d beställningar med innehåll som inte har gjorts tillgängligt.<br/><br/>Klicka på knappen för att tilldela innehåll där det är möjligt.',
        'button' => 'Leverera beställningar',
    ],
    'fulfill-precompute' => [
        'title' => 'Förberäknade albumfält',
        'description' => 'Hittade %d album med saknade förberäknade fält.<br/><br/>Motsvarar att köra: php artisan lychee:recompute-album-fields',
        'button' => 'Beräkna fält',
    ],
    'flush-queue' => [
        'title' => 'Töm kö',
        'description' => 'Hittade %d väntande jobb i kön.<br/><br/>VARNING: Om kön töms raderas alla väntande jobb permanent. Detta kan inte ångras.',
        'button' => 'Töm kö',
    ],
    'backfill-album-sizes' => [
        'title' => 'Statistik över albumstorlek',
        'description' => 'Hittade %d album utan storleksstatistik.<br/><br/>Motsvarar att köra: php artisan lychee:recompute-album-sizes',
        'button' => 'Beräkna storlekar',
    ],

    'face_quality' => [
        'title' => 'Granskning av ansiktskvalitet',
        'description' => 'Granska ansiktsdetekteringar efter kvalitetspoäng och avfärda ansikten med låg kvalitet eller felaktiga ansikten.',
        'sort_by' => 'Sortera efter:',
        'sort_confidence' => 'Konfidens',
        'sort_blur' => 'Oskärpa (Laplace)',
        'no_faces' => 'Inga ansikten att granska. Allt ser bra ut!',
        'col_face' => 'Ansikte',
        'col_person' => 'Person',
        'col_cluster' => 'Kluster',
        'col_confidence' => 'Konfidens',
        'col_blur' => 'Oskärpepoäng',
        'col_actions' => 'Åtgärder',
        'unassigned' => 'Ej tilldelad',
        'dismiss' => 'Avfärda ansikte',
        'readd' => 'Lägg till ansikte igen',
        'load_error' => 'Det gick inte att läsa in ansikten.',
        'dismissed' => 'Ansiktet avfärdades.',
        'readded' => 'Ansiktet lades till igen.',
        'dismiss_error' => 'Det gick inte att avfärda ansiktet.',
        'readd_error' => 'Det gick inte att lägga till ansiktet igen.',
        'batch_dismiss' => 'Avfärda markerade',
        'batch_dismissed' => ':count ansikte(n) avfärdades.',
        'batch_dismiss_error' => 'Det gick inte att avfärda de markerade ansiktena.',
        'batch_reactivate' => 'Återaktivera markerade',
        'batch_reactivated' => ':count ansikte(n) återaktiverades.',
        'batch_reactivate_error' => 'Det gick inte att återaktivera de markerade ansiktena.',
        'show_dismissed' => 'Visa avfärdade',
        'show_active' => 'Visa aktiva',
        'show_unassigned' => 'Endast ej tilldelade',
        'select_all' => 'Markera alla',
        'deselect_all' => 'Avmarkera alla',
        'selected_count' => ':count markerade',
    ],
    'bulk-scan-faces' => [
        'description' => 'Hittade %d foton som ännu inte har skannats för ansiktsigenkänning.<br/><br/>Kräver att AI Vision-tjänsten körs.',
    ],
    'run-clustering' => [
        'description' => 'Starta ansiktsklustring i AI Vision-tjänsten. Grupperar upptäckta ansikten efter likhet så att du kan tilldela dem till personer.',
        'success' => 'Klustringen har startats.',
    ],
    'destroy-dismissed-faces' => [
        'title' => 'Förstör avfärdade ansikten',
        'description' => 'Hittade %d avfärdade ansikten. Om de förstörs raderas deras beskurna bildfiler och inbäddningar permanent.',
        'action' => 'Förstör alla',
        'success' => 'De avfärdade ansiktena har förstörts.',
    ],
    'sync-face-embeddings' => [
        'title' => 'Synkronisera ansiktsinbäddningar',
        'description' => 'Avvikelse i antal ansikten upptäcktes (%d i skillnad). Synkroniseringen hämtar de senaste ansiktsdata från AI Vision-tjänsten till Lychee.',
        'action' => 'Synkronisera nu',
        'success' => 'Ansiktsinbäddningarna har synkroniserats.',
    ],
    'reset-face-scan-status' => [
        'title' => 'Återställ status för ansiktsskanning',
        'description' => 'Hittade %d foton med en fastnad eller misslyckad ansiktsskanning. Om de återställs kan de skannas igen.',
        'action' => 'Återställ alla',
        'success' => 'Statusen för ansiktsskanning har återställts.',
    ],

        'purge-orphan-face-embeddings' => [
            'title' => 'Rensa föräldralösa ansiktsinbäddningar',
            'description' => 'AI Vision-tjänsten kan innehålla upp till %d inbäddning(ar) som inte längre finns i Lychee. Rensningen skickar den aktuella listan över ansikten till AI Vision och raderar permanent allt som blir över.',
            'action' => 'Rensa nu',
            'success' => 'De föräldralösa ansiktsinbäddningarna har rensats.',
        ],
        'bulk-scan-nsfw' => [
        'title' => 'Massskanning för NSFW',
        'description' => 'Skanna alla oskannade foton efter NSFW-innehåll med den konfigurerade förinställningen. Kräver att NSFW-klassificeringstjänsten körs.',
        'button' => 'Skanna alla oskannade',
        'success' => 'NSFW-skanningen har startats.',
    ],
];
