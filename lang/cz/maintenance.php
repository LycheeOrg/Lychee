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
        'Oddness' => 'Nesrovnalosti',
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
        'title' => 'Kontrola kvality obličejů',
        'description' => 'Zkontrolujte detekované obličeje podle skóre kvality a zamítněte nekvalitní nebo chybné obličeje.',
        'sort_by' => 'Řadit podle:',
        'sort_confidence' => 'Spolehlivost',
        'sort_blur' => 'Rozmazání (Laplacian)',
        'no_faces' => 'Žádné odpovídající obličeje. Vše vypadá v pořádku!',
        'col_face' => 'Obličej',
        'col_person' => 'Osoba',
        'col_cluster' => 'Shluk',
        'col_confidence' => 'Spolehlivost',
        'col_blur' => 'Skóre rozmazání',
        'col_actions' => 'Akce',
        'unassigned' => 'Nepřiřazeno',
        'dismiss' => 'Zamítnout obličej',
        'readd' => 'Znovu přidat obličej',
        'load_error' => 'Obličeje se nepodařilo načíst.',
        'dismissed' => 'Obličej zamítnut.',
        'readded' => 'Obličej znovu přidán.',
        'dismiss_error' => 'Obličej se nepodařilo zamítnout.',
        'readd_error' => 'Obličej se nepodařilo znovu přidat.',
        'batch_dismiss' => 'Zamítnout vybrané',
        'batch_dismissed' => 'Zamítnuto obličejů: :count.',
        'batch_dismiss_error' => 'Vybrané obličeje se nepodařilo zamítnout.',
        'batch_reactivate' => 'Znovu aktivovat vybrané',
        'batch_reactivated' => 'Znovu aktivováno obličejů: :count.',
        'batch_reactivate_error' => 'Vybrané obličeje se nepodařilo znovu aktivovat.',
        'show_dismissed' => 'Zobrazit zamítnuté',
        'show_active' => 'Zobrazit aktivní',
        'show_unassigned' => 'Pouze nepřiřazené',
        'select_all' => 'Vybrat vše',
        'deselect_all' => 'Zrušit výběr',
        'selected_count' => 'Vybráno: :count',
    ],
    'bulk-scan-faces' => [
        'description' => 'Nalezeno %d fotografií, které dosud nebyly prohledány rozpoznáváním obličejů.<br/><br/>Vyžaduje spuštěnou službu AI Vision.',
    ],
    'run-clustering' => [
        'description' => 'Spustit shlukování obličejů ve službě AI Vision. Seskupí detekované obličeje podle podobnosti, abyste je mohli přiřadit osobám.',
        'success' => 'Shlukování bylo úspěšně spuštěno.',
    ],
    'destroy-dismissed-faces' => [
        'title' => 'Zničit zamítnuté obličeje',
        'description' => 'Nalezeno %d zamítnutých obličejů. Jejich zničením budou trvale smazány jejich výřezy a embeddingy.',
        'action' => 'Zničit vše',
        'success' => 'Zamítnuté obličeje byly úspěšně zničeny.',
    ],
    'sync-face-embeddings' => [
        'title' => 'Synchronizovat embeddingy obličejů',
        'description' => 'Zjištěn nesoulad v počtu obličejů (rozdíl %d). Synchronizace stáhne nejnovější data obličejů ze služby AI Vision do Lychee.',
        'action' => 'Synchronizovat nyní',
        'success' => 'Embeddingy obličejů byly úspěšně synchronizovány.',
    ],
    'reset-face-scan-status' => [
        'title' => 'Obnovit stav skenování obličejů',
        'description' => 'Nalezeno %d fotografií se zaseknutým nebo neúspěšným stavem skenování obličejů. Jejich obnovením umožníte nové skenování.',
        'action' => 'Obnovit vše',
        'success' => 'Stavy skenování obličejů byly úspěšně obnoveny.',
    ],

        'purge-orphan-face-embeddings' => [
            'title' => 'Vyčistit osiřelé embeddingy obličejů',
            'description' => 'Služba AI Vision může uchovávat až %d embeddingů, které již v Lychee neexistují. Vyčištění odešle aktuální seznam obličejů do AI Vision a trvale smaže vše, co zbude.',
            'action' => 'Vyčistit nyní',
            'success' => 'Osiřelé embeddingy obličejů byly úspěšně vyčištěny.',
        ],
        'bulk-scan-nsfw' => [
        'title' => 'Hromadné skenování NSFW',
        'description' => 'Prohledat všechny neskenované fotografie na obsah NSFW pomocí nastavené předvolby. Vyžaduje spuštěnou klasifikační službu NSFW.',
        'button' => 'Skenovat vše neskenované',
        'success' => 'Skenování NSFW bylo úspěšně spuštěno.',
    ],
];
