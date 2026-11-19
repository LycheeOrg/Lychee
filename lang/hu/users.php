<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Users page
    |--------------------------------------------------------------------------
    */
    'title' => 'Felhasználók',
    'description' => 'Itt kezelheti Lychee-telepítése felhasználóit. Felhasználókat hozhat létre, szerkeszthet és törölhet.',
    'create' => 'Új felhasználó létrehozása',
    'username' => 'Felhasználónév',
    'password' => 'Jelszó',
    'legend' => 'Jelmagyarázat',
    'upload_rights' => 'Ha be van jelölve, a felhasználó tartalmat tölthet fel.',
    'edit_rights' => 'Ha be van jelölve, a felhasználó módosíthatja a profilját (felhasználónév, jelszó).',
    'upload_trust_level' => 'Feltöltési bizalmi szint – meghatározza, hogy a feltöltések azonnal nyilvánosak-e.',

    'quota' => 'Ha be van állítva, a felhasználónak tárhelykvótája van a képekhez (kB-ban).',
    'user_deleted' => 'Felhasználó törölve',
    'user_created' => 'Felhasználó létrehozva',
    'user_updated' => 'Felhasználó frissítve',
    'change_saved' => 'Módosítás mentve!',
    'create_edit' => [
        'upload_rights' => 'A felhasználó tartalmat tölthet fel.',
        'edit_rights' => 'A felhasználó módosíthatja a profilját (felhasználónév, jelszó).',
        'admin_rights' => 'A felhasználó rendszergazdai jogokkal rendelkezik.',
        'upload_trust_level' => 'Feltöltési bizalmi szint',
        'upload_trust_level_check' => 'Ellenőrzés – a feltöltések nyilvánossá válásához rendszergazdai jóváhagyás szükséges.',
        'upload_trust_level_monitor' => 'Megfigyelés – a feltöltések nyilvánosak, hacsak a tartalmuk miatt meg nem jelölik őket.',
        'upload_trust_level_trust_but_verify' => 'Bízz, de ellenőrizz – a feltöltések nyilvánosak; az ellenőrzési találatok automatikusan jóváhagyva, a tiltási találatok kezelése beállítható.',
        'upload_trust_level_trusted' => 'Megbízható – a feltöltések azonnal nyilvánosak.',
        'trust_level_options' => [
            'trusted' => 'Megbízható',
            'trust_but_verify' => 'Bízz, de ellenőrizz',
            'monitor' => 'Megfigyelés',
            'check' => 'Ellenőrzés',
        ],
        'quota' => 'A felhasználónak kvótakorlátja van.',
        'quota_kb' => 'kvóta kB-ban (0 az alapértelmezetthez)',
        'note' => 'Rendszergazdai megjegyzés (nem nyilvános)',
        'create' => 'Létrehozás',
        'edit' => 'Szerkesztés',
    ],
    'invite' => [
        'button' => 'Felhasználó meghívása',
        'links_are_not_revokable' => 'Invitation links are not revokable.',
        'link_is_valid_x_days' => 'Ez a hivatkozás %d napig érvényes.',
    ],
    'line' => [
        'owner' => 'Tulajdonos',
        'admin' => 'Rendszergazda',
        'edit' => 'Szerkesztés',
        'delete' => 'Törlés',
    ],
];
