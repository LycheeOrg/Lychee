<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Users page
    |--------------------------------------------------------------------------
    */
    'title' => 'Uživatelé',
    'description' => 'Zde můžete spravovat uživatele této instalace Lychee. Uživatele můžete vytvářet, upravovat a mazat.',
    'create' => 'Vytvořit nového uživatele',
    'username' => 'Přihlašovací jméno',
    'password' => 'Heslo',
    'legend' => 'Legenda',
    'upload_rights' => 'Pokud povoleno, uživatel může nahrávat obsah.',
    'edit_rights' => 'Pokud vybráno, uživatel může měnit svůj profil (přihlašovací jméno, heslo).',
    'upload_trust_level' => 'Úroveň důvěryhodnosti při nahrávání — určuje, jestli je nahraný obsah okamžitě zveřejněný.',

    'quota' => 'Pokud nastaveno, určuje limit objemu nahraných fotografií (v kB).',
    'user_deleted' => 'Uživatel smazán',
    'user_created' => 'Uživatel vytvořen',
    'user_updated' => 'Uživatel aktualizován',
    'change_saved' => 'Změny jsou uloženy!',
    'create_edit' => [
        'upload_rights' => 'Uživatel může nahrávat obsah.',
        'edit_rights' => 'Uživatel může měnit svůj profil (přihlašovací jméno, heslo).',
        'admin_rights' => 'Uživatel má práva administrátora.',
        'upload_trust_level' => 'Úroveň důvěry při nahrávání',
        'upload_trust_level_check' => 'Kontrola – nahraný obsah vyžaduje schválení administrátora.',
        'upload_trust_level_monitor' => 'Dohled - nahraný obsah veřejně přístupný, pokud není označen jako nevhodný.',
        'upload_trust_level_trusted' => 'Důvěryhodný - nahraný obsah je okamžitě zveřejněn.',

        'quota' => 'Uživatel má nastavený limit objemu.',
        'quota_kb' => 'limit v kB (0 pro přednastavenou hodnotu)',
        'note' => 'Poznámka admina (není veřejně viditelná)',
        'create' => 'Vytvořit',
        'edit' => 'Upravit',
    ],
    'invite' => [
        'button' => 'Pozvat uživatele',
        'links_are_not_revokable' => 'Odkaz pozvánky nelze zrušit.',
        'link_is_valid_x_days' => 'Odkaz je validní %d dní.',
    ],
    'line' => [
        'owner' => 'Vlastník',
        'admin' => 'Administrátor',
        'edit' => 'Upravit',
        'delete' => 'Smazat',
    ],
];
