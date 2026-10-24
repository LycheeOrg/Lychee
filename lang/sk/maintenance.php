<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Update Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Údržba',
    'description' => 'Na tejto stránke nájdete všetky potrebné akcie, aby vaša inštalácia Lychee fungovala hladko a spoľahlivo.',
    'cleaning' => [
        'title' => 'Čistenie %s',
        'result' => '%s odstránené.',
        'description' => 'Odstrániť celý obsah z <span class="font-mono">%s</span>',
        'button' => 'Vyčistiť',
    ],
    'duplicate-finder' => [
        'title' => 'Duplikáty',
        'description' => 'Tento modul spočíta potenciálne duplikáty medzi obrázkami.',
        'duplicates-all' => 'Duplikáty vo všetkých albumoch',
        'duplicates-title' => 'Title duplicates per album',
        'duplicates-per-album' => 'Duplikáty podľa albumu',
        'show' => 'Zobraziť duplikáty',
        'load' => 'Načítať počty',
    ],
    'fix-jobs' => [
        'title' => 'Oprava histórie úloh',
        'description' => 'Označiť úlohy so stavom <span class="text-ready-400">%s</span> alebo <span class="text-primary-500">%s</span> ako <span class="text-danger-700">%s</span>.',
        'button' => 'Opraviť históriu úloh',
    ],
    'gen-sizevariants' => [
        'title' => 'Chýbajúce %s',
        'description' => 'Nájdených %d %s, ktoré je možné vygenerovať.',
        'button' => 'Vygenerovať!',
        'success' => 'Úspešne vygenerovaných %d %s.',
    ],
    'fill-filesize-sizevariants' => [
        'title' => 'Chýbajúce veľkosti súborov',
        'description' => 'Nájdených %d malých variantov bez veľkosti súboru.',
        'button' => 'Načítať údaje!',
        'success' => 'Úspešne vypočítané veľkosti %d malých variantov.',
    ],
    'fix-tree' => [
        'title' => 'Štatistiky stromu',
        'Oddness' => 'Nepárnosť',
        'Duplicates' => 'Duplikáty',
        'Wrong parents' => 'Nesprávni rodičia',
        'Missing parents' => 'Chýbajúci rodičia',
        'button' => 'Opraviť strom',
    ],
    'optimize' => [
        'title' => 'Optimalizovať databázu',
        'description' => 'Ak zaznamenáte spomalenie vašej inštalácie, môže to byť tým, že vaša databáza nemá všetky potrebné indexy.',
        'button' => 'Optimalizovať databázu',
    ],
    'missing-palettes' => [
        'title' => 'Chýbajúce palety',
        'description' => 'Nájdených %d chýbajúcich paliet.',
        'button' => 'Vytvoriť chýbajúce',
    ],
    'statistics-check' => [
        'title' => 'Kontrola integrity štatistík',
        'missing_photos' => 'Chýba %d štatistík fotografií.',
        'missing_albums' => 'Chýba %d štatistík albumov.',
        'button' => 'Vytvoriť chýbajúce',
    ],
    'flush-cache' => [
        'title' => 'Vyprázdniť cache',
        'description' => 'Vyprázdniť cache všetkých používateľov na vyriešenie problémov s neplatnosťou.',
        'button' => 'Vyprázdniť',
    ],
    'old-orders' => [
        'title' => 'Staré objednávky',
        'description' => 'Nájdených %d starých objednávok.<br/>Stará objednávka je staršia ako 14 dní, nemá priradeného používateľa a buď stále čaká na platbu, alebo neobsahuje žiadne položky.',
        'button' => 'Odstrániť staré objednávky',
    ],
    'fulfill-orders' => [
        'title' => 'Objednávky na vybavenie',
        'description' => 'Nájdených %d objednávok s obsahom, ktorý ešte nebol sprístupnený.<br/>Kliknutím na tlačidlo priradíte obsah, ak je to možné.',
        'button' => 'Vybaviť objednávky',
    ],
    'fulfill-precompute' => [
        'title' => 'Predpočítané polia albumov',
        'description' => 'Nájdených %d albumov s chýbajúcimi predpočítanými poliami.<br/>Ekvivalent spustenia: php artisan lychee:recompute-album-stats',
        'button' => 'Vypočítať polia',
    ],
    'flush-queue' => [
        'title' => 'Vyprázdniť frontu',
        'description' => 'Nájdených %d čakajúcich úloh vo fronte.<br/>POZOR: Vyprázdnenie fronty natrvalo odstráni všetky čakajúce úlohy. Túto akciu nie je možné vrátiť späť.',
        'button' => 'Vyprázdniť frontu',
    ],
    'backfill-album-sizes' => [
        'title' => 'Štatistiky veľkosti albumov',
        'description' => 'Nájdených %d albumov bez štatistík veľkosti.<br/>Ekvivalent spustenia: php artisan lychee:recompute-album-sizes',
        'description_queue' => 'Prepočíta na pozadí štatistiky veľkosti všetkých albumov.<br/>Ekvivalent spustenia: php artisan lychee:recompute-album-sizes',
        'button' => 'Vypočítať veľkosti',
    ],
    'recompute-buckets' => [
        'title' => 'Skupiny časovej osi',
        'description' => 'Prepočíta skupinu časovej osi každého albumu a každej fotografie a potom obnoví zoznamy albumov a fotografií.<br/>Zodpovedá príkazu: php artisan lychee:recompute-buckets',
        'button' => 'Prepočítať',
    ],

    'face_quality' => [
        'title' => 'Kontrola kvality tvárí',
        'description' => 'Skontrolujte rozpoznané tváre podľa skóre kvality a zamietnite nekvalitné alebo chybné tváre.',
        'sort_by' => 'Zoradiť podľa:',
        'sort_confidence' => 'Spoľahlivosť',
        'sort_blur' => 'Rozmazanie (Laplacian)',
        'no_faces' => 'Žiadne vyhovujúce tváre. Všetko vyzerá v poriadku!',
        'col_face' => 'Tvár',
        'col_person' => 'Osoba',
        'col_cluster' => 'Zhluk',
        'col_confidence' => 'Spoľahlivosť',
        'col_blur' => 'Skóre rozmazania',
        'col_actions' => 'Akcie',
        'unassigned' => 'Nepriradené',
        'dismiss' => 'Zamietnuť tvár',
        'readd' => 'Znova pridať tvár',
        'load_error' => 'Nepodarilo sa načítať tváre.',
        'dismissed' => 'Tvár zamietnutá.',
        'readded' => 'Tvár znova pridaná.',
        'dismiss_error' => 'Nepodarilo sa zamietnuť tvár.',
        'readd_error' => 'Nepodarilo sa znova pridať tvár.',
        'batch_dismiss' => 'Zamietnuť vybrané',
        'batch_dismissed' => 'Zamietnuté tváre: :count.',
        'batch_dismiss_error' => 'Nepodarilo sa zamietnuť vybrané tváre.',
        'batch_reactivate' => 'Znova aktivovať vybrané',
        'batch_reactivated' => 'Znova aktivované tváre: :count.',
        'batch_reactivate_error' => 'Nepodarilo sa znova aktivovať vybrané tváre.',
        'show_dismissed' => 'Zobraziť zamietnuté',
        'show_active' => 'Zobraziť aktívne',
        'show_unassigned' => 'Iba nepriradené',
        'select_all' => 'Vybrať všetko',
        'deselect_all' => 'Zrušiť výber',
        'selected_count' => 'Vybrané: :count',
    ],
    'bulk-scan-faces' => [
        'description' => 'Nájdených %d fotografií, ktoré ešte neboli skenované na rozpoznávanie tvárí.<br/>Vyžaduje spustenú službu AI Vision.',
    ],
    'run-clustering' => [
        'description' => 'Spustí zhlukovanie tvárí v službe AI Vision. Zoskupí rozpoznané tváre podľa podobnosti, aby ste ich mohli priradiť osobám.',
        'success' => 'Zhlukovanie bolo úspešne spustené.',
    ],
    'destroy-dismissed-faces' => [
        'title' => 'Zničiť zamietnuté tváre',
        'description' => 'Nájdených %d zamietnutých tvárí. Ich zničením sa natrvalo odstránia ich výrezy a embeddingy.',
        'action' => 'Zničiť všetko',
        'success' => 'Zamietnuté tváre boli úspešne zničené.',
    ],
    'sync-face-embeddings' => [
        'title' => 'Synchronizovať embeddingy tvárí',
        'description' => 'Zistený nesúlad v počte tvárí (rozdiel %d). Synchronizácia stiahne najnovšie údaje o tvárach zo služby AI Vision do Lychee.',
        'action' => 'Synchronizovať teraz',
        'success' => 'Embeddingy tvárí boli úspešne synchronizované.',
    ],
    'reset-face-scan-status' => [
        'title' => 'Obnoviť stav skenovania tvárí',
        'description' => 'Nájdených %d fotografií so zaseknutým alebo neúspešným stavom skenovania tvárí. Ich obnovením bude možné ich znova skenovať.',
        'action' => 'Obnoviť všetko',
        'success' => 'Stavy skenovania tvárí boli úspešne obnovené.',
    ],

        'purge-orphan-face-embeddings' => [
            'title' => 'Vyčistiť osirelé embeddingy tvárí',
            'description' => 'Služba AI Vision môže uchovávať až %d embeddingov, ktoré už v Lychee neexistujú. Vyčistenie odošle aktuálny zoznam tvárí do AI Vision a natrvalo odstráni všetko, čo zostane.',
            'action' => 'Vyčistiť teraz',
            'success' => 'Osirelé embeddingy tvárí boli úspešne vyčistené.',
        ],
        'bulk-scan-nsfw' => [
        'title' => 'Hromadné skenovanie NSFW',
        'description' => 'Skenovať všetky neskenované fotografie na obsah NSFW pomocou nakonfigurovanej predvoľby. Vyžaduje spustenú službu klasifikácie NSFW.',
        'button' => 'Skenovať všetky neskenované',
        'success' => 'Skenovanie NSFW bolo úspešne spustené.',
    ],
];
