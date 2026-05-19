<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Profile page
    |--------------------------------------------------------------------------
    */
    'title' => 'Uživatelský profil',
    'login' => [
        'header' => 'Profil',
        'enter_current_password' => 'Zadejte své aktuální heslo:',
        'current_password' => 'Aktuální heslo',
        'credentials_update' => 'Vaše přihlašovací údaje budou změněny na následující:',
        'username' => 'Uživatelské jméno',
        'new_password' => 'Nové heslo',
        'confirm_new_password' => 'Potvrďte nové heslo',
        'email_instruction' => 'Zadejte níže svou e-mailovou adresu, abyste mohli dostávat e-mailová oznámení. Chcete-li odběr e-mailů zrušit, jednoduše smažte e-mailovou adresu.',
        'email' => 'E-mail',
        'change' => 'Změnit přihlašovací údaje',
        'api_token' => 'API token ...',
        'missing_fields' => 'Chybějící pole',
        'ldap_managed' => 'Přihlašovací údaje uživatele jsou spravovány pomocí LDAP.',
    ],
    'register' => [
        'username_exists' => 'Uživatelské jméno již existuje.',
        'password_mismatch' => 'Hesla se neshodují.',
        'signup' => 'Zaregistrovat se',
        'error' => 'Při registraci účtu došlo k chybě.',
        'success' => 'Váš účet byl úspěšně vytvořen.',
    ],
    'token' => [
        'unavailable' => 'Tento token jste již zobrazili.',
        'no_data' => 'Nebyl vygenerován žádný API token.',
        'disable' => 'Zakázat',
        'disabled' => 'Token je zakázán',
        'warning' => 'Tento token se již znovu nezobrazí. Zkopírujte si jej a uložte na bezpečné místo.',
        'reset' => 'Obnovit token',
        'create' => 'Vytvořit nový token',
    ],
    'oauth' => [
        'header' => 'OAuth',
        'header_not_available' => 'OAuth není k dispozici',
        'setup_env' => 'Nastavte přihlašovací údaje v souboru .env',
        'token_registered' => 'Token %s byl zaregistrován.',
        'setup' => 'Nastavit %s',
        'reset' => 'Obnovit',
        'credential_deleted' => 'Přihlašovací údaje smazány!',
    ],
    'u2f' => [
        'header' => 'Passkey/MFA/2FA',
        'info' => 'Tato možnost umožňuje pouze ověřování pomocí WebAuthn namísto uživatelského jména a hesla.',
        'empty' => 'Seznam přihlašovacích údajů je prázdný!',
        'not_secure' => 'Prostředí není zabezpečené. U2F není k dispozici.',
        'new' => 'Zaregistrujte nové zařízení.',
        'credential_deleted' => 'Přihlašovací údaje smazány!',
        'credential_updated' => 'Přihlašovací údaje aktualizovány!',
        'credential_registred' => 'Registrace byla úspěšná!',
        '5_chars' => 'Minimálně 5 znaků.',
    ],
    'preferences' => [
        'header' => 'Nastavení',
        'save' => 'Uložit nastavení',
        'reset' => 'Reset',
        'change_saved' => 'Nastavení uloženo!',
    ],
    'shared_albums' => [
        'instruction' => 'Zvolte, jak se sdílená alba (alba od jiných uživatelů) zobrazí ve vaší galerii:',
        'mode_default' => 'Použít výchozí nastavení serveru',
        'mode_default_desc' => 'Použije výchozí režim viditelnosti serveru.',
        'mode_show' => 'Zobrazit v řádku',
        'mode_show_desc' => 'Sdílená alba se zobrazují pod vašimi vlastními alby.',
        'mode_separate' => 'Samostatné záložky',
        'mode_separate_desc' => 'Zobrazit alba v samostatných záložkách „Moje alba“ a „Sdílené se mnou“.',
        'mode_separate_shared_only' => 'Pouze sdílená',
        'mode_separate_shared_only_desc' => 'Samostatné záložky zobrazující pouze přímo sdílená alba (kromě veřejných alb).',
        'mode_hide' => 'Skrýt',
        'mode_hide_desc' => 'Nezobrazovat žádná sdílená alba.',
    ],
];
