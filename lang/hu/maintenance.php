<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Update Page
    |--------------------------------------------------------------------------
    */
    'title' => 'Karbantartás',
    'description' => 'Ezen az oldalon megtalálja mindazokat a műveleteket, amelyek a Lychee telepítés zavartalan és megbízható működéséhez szükségesek.',
    'cleaning' => [
        'title' => '%s tisztítása',
        'result' => '%s törölve.',
        'description' => 'Minden tartalom eltávolítása innen: <span class="font-mono">%s</span>',
        'button' => 'Tisztítás',
    ],
    'duplicate-finder' => [
        'title' => 'Duplikátumok',
        'description' => 'Ez a modul megszámolja a képek közötti lehetséges duplikátumokat.',
        'duplicates-all' => 'Duplikátumok az összes albumban',
        'duplicates-title' => 'Title duplicates per album',
        'duplicates-per-album' => 'Duplikátumok albumonként',
        'show' => 'Duplikátumok megjelenítése',
        'load' => 'Számok betöltése',
    ],
    'fix-jobs' => [
        'title' => 'Feladatelőzmények javítása',
        'description' => 'A(z) <span class="text-ready-400">%s</span> vagy <span class="text-primary-500">%s</span> állapotú feladatok megjelölése <span class="text-danger-700">%s</span> állapotúként.',
        'button' => 'Feladatelőzmények javítása',
    ],
    'gen-sizevariants' => [
        'title' => 'Hiányzó %s',
        'description' => '%d %s található, amely létrehozható.',
        'button' => 'Létrehozás!',
        'success' => 'Sikeresen létrehozva: %d %s.',
    ],
    'fill-filesize-sizevariants' => [
        'title' => 'Hiányzó fájlméretek',
        'description' => '%d kis méretváltozat található fájlméret nélkül.',
        'button' => 'Adatok lekérése!',
        'success' => '%d kis méretváltozat mérete sikeresen kiszámítva.',
    ],
    'fix-tree' => [
        'title' => 'Fastatisztika',
        'Oddness' => 'Rendellenességek',
        'Duplicates' => 'Duplikátumok',
        'Wrong parents' => 'Hibás szülők',
        'Missing parents' => 'Hiányzó szülők',
        'button' => 'Fa javítása',
    ],
    'optimize' => [
        'title' => 'Adatbázis optimalizálása',
        'description' => 'Ha lassulást tapasztal a telepítésben, annak oka lehet, hogy az adatbázisból hiányzik néhány szükséges index.',
        'button' => 'Adatbázis optimalizálása',
    ],
    'missing-palettes' => [
        'title' => 'Hiányzó paletták',
        'description' => '%d hiányzó paletta található.',
        'button' => 'Hiányzók létrehozása',
    ],
    'statistics-check' => [
        'title' => 'Statisztikák integritásának ellenőrzése',
        'missing_photos' => '%d fénykép statisztikája hiányzik.',
        'missing_albums' => '%d album statisztikája hiányzik.',
        'button' => 'Hiányzók létrehozása',
    ],
    'flush-cache' => [
        'title' => 'Gyorsítótár ürítése',
        'description' => 'Minden felhasználó gyorsítótárának ürítése az érvénytelenítési problémák megoldásához.',
        'button' => 'Ürítés',
    ],
    'old-orders' => [
        'title' => 'Régi rendelések',
        'description' => '%d régi rendelés található.<br/><br/>Régi rendelésnek számít az a 14 napnál régebbi rendelés, amelyhez nem tartozik felhasználó, és vagy még fizetésre vár, vagy nincs benne tétel.',
        'button' => 'Régi rendelések törlése',
    ],
    'fulfill-orders' => [
        'title' => 'Teljesítendő rendelések',
        'description' => '%d rendelés található, amelynek tartalma még nem lett elérhetővé téve.<br/><br/>Kattintson a gombra a tartalom hozzárendeléséhez, ahol lehetséges.',
        'button' => 'Rendelések teljesítése',
    ],
    'fulfill-precompute' => [
        'title' => 'Album előre számított mezői',
        'description' => '%d album található hiányzó előre számított mezőkkel.<br/><br/>Egyenértékű ennek futtatásával: php artisan lychee:recompute-album-fields',
        'button' => 'Mezők kiszámítása',
    ],
    'flush-queue' => [
        'title' => 'Várólista ürítése',
        'description' => '%d függőben lévő feladat található a várólistában.<br/><br/>FIGYELEM: A várólista ürítése véglegesen törli az összes függőben lévő feladatot. Ez nem vonható vissza.',
        'button' => 'Várólista ürítése',
    ],
    'backfill-album-sizes' => [
        'title' => 'Albumméret-statisztikák',
        'description' => '%d album található méretstatisztika nélkül.<br/><br/>Egyenértékű ennek futtatásával: php artisan lychee:recompute-album-sizes',
        'button' => 'Méretek kiszámítása',
    ],

    'face_quality' => [
        'title' => 'Arcminőség áttekintése',
        'description' => 'Az arcfelismerések áttekintése minőségi pontszám szerint, és az alacsony minőségű vagy hibás arcok elvetése.',
        'sort_by' => 'Rendezés:',
        'sort_confidence' => 'Megbízhatóság',
        'sort_blur' => 'Elmosódottság (Laplace)',
        'no_faces' => 'Nincs megfelelő arc. Minden rendben van!',
        'col_face' => 'Arc',
        'col_person' => 'Személy',
        'col_cluster' => 'Csoport',
        'col_confidence' => 'Megbízhatóság',
        'col_blur' => 'Elmosódottsági pontszám',
        'col_actions' => 'Műveletek',
        'unassigned' => 'Hozzárendeletlen',
        'dismiss' => 'Arc elvetése',
        'readd' => 'Arc visszaadása',
        'load_error' => 'Nem sikerült betölteni az arcokat.',
        'dismissed' => 'Arc elvetve.',
        'readded' => 'Arc visszaadva.',
        'dismiss_error' => 'Nem sikerült elvetni az arcot.',
        'readd_error' => 'Nem sikerült visszaadni az arcot.',
        'batch_dismiss' => 'Kijelöltek elvetése',
        'batch_dismissed' => ':count arc elvetve.',
        'batch_dismiss_error' => 'Nem sikerült elvetni a kijelölt arcokat.',
        'batch_reactivate' => 'Kijelöltek újraaktiválása',
        'batch_reactivated' => ':count arc újraaktiválva.',
        'batch_reactivate_error' => 'Nem sikerült újraaktiválni a kijelölt arcokat.',
        'show_dismissed' => 'Elvetettek megjelenítése',
        'show_active' => 'Aktívak megjelenítése',
        'show_unassigned' => 'Csak hozzárendeletlenek',
        'select_all' => 'Összes kijelölése',
        'deselect_all' => 'Kijelölés megszüntetése',
        'selected_count' => ':count kijelölve',
    ],
    'bulk-scan-faces' => [
        'description' => '%d fénykép található, amelyet még nem vizsgáltak meg arcfelismeréssel.<br/><br/>Ehhez futnia kell az AI Vision szolgáltatásnak.',
    ],
    'run-clustering' => [
        'description' => 'Arccsoportosítás indítása az AI Vision szolgáltatásban. Hasonlóság alapján csoportosítja az észlelt arcokat, így személyekhez rendelheti őket.',
        'success' => 'A csoportosítás sikeresen elindult.',
    ],
    'destroy-dismissed-faces' => [
        'title' => 'Elvetett arcok megsemmisítése',
        'description' => '%d elvetett arc található. Megsemmisítésük véglegesen törli a kivágott képfájlokat és a beágyazásokat.',
        'action' => 'Összes megsemmisítése',
        'success' => 'Az elvetett arcok sikeresen megsemmisítve.',
    ],
    'sync-face-embeddings' => [
        'title' => 'Arcbeágyazások szinkronizálása',
        'description' => 'Eltérés az arcok számában (%d különbség). A szinkronizálás lekéri a legfrissebb arcadatokat az AI Vision szolgáltatásból a Lychee-be.',
        'action' => 'Szinkronizálás most',
        'success' => 'Az arcbeágyazások sikeresen szinkronizálva.',
    ],
    'reset-face-scan-status' => [
        'title' => 'Arcvizsgálati állapot visszaállítása',
        'description' => '%d fénykép található beragadt függőben lévő vagy sikertelen arcvizsgálati állapottal. A visszaállítás lehetővé teszi az újbóli vizsgálatukat.',
        'action' => 'Összes visszaállítása',
        'success' => 'Az arcvizsgálati állapotok sikeresen visszaállítva.',
    ],

        'purge-orphan-face-embeddings' => [
            'title' => 'Árva arcbeágyazások törlése',
            'description' => 'Az AI Vision szolgáltatás legfeljebb %d olyan beágyazást tárolhat, amely már nem létezik a Lychee-ben. A törlés elküldi az arcok aktuális listáját az AI Vision szolgáltatásnak, és véglegesen töröl minden fennmaradó elemet.',
            'action' => 'Törlés most',
            'success' => 'Az árva arcbeágyazások sikeresen törölve.',
        ],
        'bulk-scan-nsfw' => [
        'title' => 'Tömeges NSFW-vizsgálat',
        'description' => 'Az összes még nem vizsgált fénykép ellenőrzése NSFW tartalomra a beállított előbeállítással. Ehhez futnia kell az NSFW osztályozó szolgáltatásnak.',
        'button' => 'Összes vizsgálatlan ellenőrzése',
        'success' => 'Az NSFW-vizsgálat sikeresen elindítva.',
    ],
];
