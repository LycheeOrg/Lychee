<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Renamer Rules
    |--------------------------------------------------------------------------
    */

    // Page title
    'title' => 'Átnevezési szabályok',

    // Modal titles
    'create_rule' => 'Átnevezési szabály létrehozása',
    'edit_rule' => 'Átnevezési szabály szerkesztése',

    // Form fields
    'rule_name' => 'Szabály neve',
    'description' => 'Leírás',
    'pattern' => 'Minta',
    'replacement' => 'Csere',
    'mode' => 'Mód',
    'order' => 'Sorrend',
    'enabled' => 'Engedélyezve',
    'photo_rule' => 'Fényképekre alkalmazott szabály',
    'album_rule' => 'Albumokra alkalmazott szabály',

    // Form placeholders and help text
    'description_placeholder' => 'Opcionális leírás arról, mit csinál ez a szabály',
    'pattern_help' => 'Illesztendő minta (pl. IMG_, DSC_)',
    'replacement_help' => 'Csereszöveg (pl. Photo_, Camera_)',
    'order_help' => 'A kisebb számok kerülnek először feldolgozásra (1 = legmagasabb prioritás)',
    'enabled_help' => '(Átnevezéskor csak az engedélyezett szabályok lesznek alkalmazva)',

    // Mode options
    'mode_first' => 'Első előfordulás',
    'mode_all' => 'Összes előfordulás',
    'mode_regex' => 'Reguláris kifejezés',
    'mode_trim' => 'Szóközök levágása',
    'mode_strtolower' => 'kisbetűs',
    'mode_strtoupper' => 'NAGYBETŰS',
    'mode_ucwords' => 'Minden Szó Nagybetűvel',
    'mode_ucfirst' => 'Nagy kezdőbetű',

    'mode_first_description' => 'Csak az első előfordulás cseréje',
    'mode_all_description' => 'Összes előfordulás cseréje',
    'mode_regex_description' => 'Reguláris kifejezéses mintaillesztés használata',
    'mode_trim_description' => 'Szóközök levágása',
    'mode_strtolower_description' => 'Szöveg átalakítása kisbetűssé',
    'mode_strtoupper_description' => 'Szöveg átalakítása NAGYBETŰSSÉ',
    'mode_ucwords_description' => 'Minden szó nagy kezdőbetűvel',
    'mode_ucfirst_description' => 'Csak az első betű nagybetűs',

    'regex_help' => 'Használjon reguláris kifejezéseket a mintaillesztéshez. Például az <code>IMG_1234.jpeg</code> <code>1234_JPG.jpeg</code> névre cseréléséhez használhatja a <code>/IMG_(\\d+)/</code> kifejezést keresendő mintaként és a <code>$1_JPG</code> kifejezést csereként. További magyarázatokat és példákat az alábbi linkeken talál.',

    // Buttons
    'cancel' => 'Mégse',
    'create' => 'Létrehozás',
    'update' => 'Frissítés',
    'create_first_rule' => 'Hozza létre az első szabályát',

    // Validation messages
    'rule_name_required' => 'A szabály neve kötelező',
    'pattern_required' => 'A minta kötelező',
    'replacement_required' => 'A csere kötelező',
    'mode_required' => 'A mód kötelező',
    'order_positive' => 'A sorrendnek pozitív számnak kell lennie',

    // Success messages
    'rule_created' => 'Az átnevezési szabály sikeresen létrehozva',
    'rule_updated' => 'Az átnevezési szabály sikeresen frissítve',
    'rule_deleted' => 'Az átnevezési szabály sikeresen törölve',

    // Error messages
    'failed_to_create' => 'Nem sikerült létrehozni az átnevezési szabályt',
    'failed_to_update' => 'Nem sikerült frissíteni az átnevezési szabályt',
    'failed_to_delete' => 'Nem sikerült törölni az átnevezési szabályt',
    'failed_to_load' => 'Nem sikerült betölteni az átnevezési szabályokat',

    // List view
    'rules_count' => ':count szabály',
    'no_rules' => 'Nem találhatók átnevezési szabályok',
    'loading' => 'Átnevezési szabályok betöltése...',
    'pattern_label' => 'Minta',
    'replace_with_label' => 'Csere erre',
    'photo' => 'Fénykép',
    'album' => 'Album',

    // Delete confirmation
    'confirm_delete_header' => 'Törlés megerősítése',
    'confirm_delete_message' => 'Biztosan törölni szeretné a(z) ":rule" szabályt?',
    'delete' => 'Törlés',

    // Status messages
    'success' => 'Siker',
    'error' => 'Hiba',

    // Placeholders
    'select_mode' => 'Válassza ki az átnevezési módot',
    'execution_order' => 'Végrehajtási sorrend',

    // Test functionality
    'test_input_placeholder' => 'Adjon meg egy fájlnevet az átnevezési szabályok teszteléséhez (pl. IMG_1234.jpg)',
    'test_original' => 'Original',
    'test_result' => 'Eredmény',
    'test_failed' => 'Nem sikerült tesztelni az átnevezési szabályokat',
    'apply_photo_rules' => 'Fényképszabályok alkalmazása',
    'apply_album_rules' => 'Albumszabályok alkalmazása',
];
