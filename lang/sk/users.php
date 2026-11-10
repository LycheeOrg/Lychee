<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Users page
    |--------------------------------------------------------------------------
    */
    'title' => 'Používatelia',
    'description' => 'Tu môžete spravovať používateľov vašej inštalácie Lychee. Môžete vytvárať, upravovať a odstraňovať používateľov.',
    'create' => 'Vytvoriť nového používateľa',
    'username' => 'Používateľské meno',
    'password' => 'Heslo',
    'legend' => 'Legenda',
    'upload_rights' => 'Ak je zaškrtnuté, používateľ môže nahrávať obsah.',
    'edit_rights' => 'Ak je zaškrtnuté, používateľ môže upravovať svoj profil (používateľské meno, heslo).',
    'upload_trust_level' => 'Úroveň dôvery pri nahrávaní — určuje, či sú nahrané súbory hneď verejné.',

    'quota' => 'Ak je nastavené, používateľ má kvótu úložiska pre obrázky (v kB).',
    'user_deleted' => 'Používateľ odstránený',
    'user_created' => 'Používateľ vytvorený',
    'user_updated' => 'Používateľ aktualizovaný',
    'change_saved' => 'Zmena uložená!',
    'create_edit' => [
        'upload_rights' => 'Používateľ môže nahrávať obsah.',
        'edit_rights' => 'Používateľ môže upravovať svoj profil (používateľské meno, heslo).',
        'admin_rights' => 'Používateľ má administrátorské práva.',
        'upload_trust_level' => 'Úroveň dôvery pri nahrávaní',
        'upload_trust_level_check' => 'Kontrola – nahrané súbory pred zverejnením vyžadujú schválenie administrátorom.',
        'upload_trust_level_monitor' => 'Sledovanie – nahrané súbory sú verejné, pokiaľ nie je ich obsah označený.',
        'upload_trust_level_trust_but_verify' => 'Dôveruj, ale preveruj – nahrané súbory sú verejné; nálezy na kontrolu sa schvaľujú automaticky, nálezy na blokovanie sú konfigurovateľné.',
        'upload_trust_level_trusted' => 'Dôveryhodný – nahrané súbory sú hneď verejné.',
        'trust_level_options' => [
            'trusted' => 'Dôveryhodný',
            'trust_but_verify' => 'Dôveruj, ale preveruj',
            'monitor' => 'Sledovanie',
            'check' => 'Kontrola',
        ],
        'quota' => 'Používateľ má obmedzenie kvóty.',
        'quota_kb' => 'kvóta v kB (0 pre predvolenú)',
        'note' => 'Poznámka administrátora (nie je verejne viditeľná)',
        'create' => 'Vytvoriť',
        'edit' => 'Upraviť',
    ],
    'invite' => [
        'button' => 'Pozvať používateľa',
        'links_are_not_revokable' => 'Invitation links are not revokable.',
        'link_is_valid_x_days' => 'Tento odkaz je platný %d dní.',
    ],
    'line' => [
        'owner' => 'Vlastník',
        'admin' => 'Administrátor',
        'edit' => 'Upraviť',
        'delete' => 'Odstrániť',
    ],
];
