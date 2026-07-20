<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Profile page
    |--------------------------------------------------------------------------
    */
    'title' => 'Profiel',
    'login' => [
        'header' => 'Profiel',
        'enter_current_password' => 'Voer uw huidige wachtwoord in:',
        'current_password' => 'Huidig wachtwoord',
        'credentials_update' => 'Uw inloggegevens worden gewijzigd in het volgende:',
        'username' => 'Gebruikersnaam',
        'new_password' => 'Nieuw wachtwoord',
        'confirm_new_password' => 'Bevestig nieuw wachtwoord',
        'email_instruction' => 'Voeg hieronder uw e-mailadres toe om e-mailmeldingen te ontvangen. Om geen e-mails meer te ontvangen, verwijdert u eenvoudig uw e-mailadres hieronder.',
        'email' => 'E-mail',
        'change' => 'Inloggegevens wijzigen',
        'api_token' => 'API-token …',
        'missing_fields' => 'Ontbrekende velden',
        'ldap_managed' => 'Gebruikerslogingegevens worden beheerd door LDAP.',
    ],
    'register' => [
        'username_exists' => 'Gebruikersnaam bestaat al.',
        'password_mismatch' => 'De wachtwoorden komen niet overeen.',
        'signup' => 'Aanmelden',
        'error' => 'Er is een fout opgetreden bij het registreren van uw account.',
        'success' => 'Uw account is succesvol aangemaakt.',
    ],
    'token' => [
        'unavailable' => 'U heeft dit token al bekeken.',
        'no_data' => 'Er zijn geen API-tokens gegenereerd.',
        'disable' => 'Uitschakelen',
        'disabled' => 'Token uitgeschakeld',
        'warning' => 'Dit token wordt niet opnieuw weergegeven. Kopieer het en bewaar het op een veilige plaats.',
        'reset' => 'Token opnieuw instellen',
        'create' => 'Nieuw token aanmaken',
    ],
    'oauth' => [
        'header' => 'OAuth',
        'header_not_available' => 'OAuth is niet beschikbaar',
        'setup_env' => 'Stel de referenties in uw .env in',
        'token_registered' => '%s token geregistreerd.',
        'setup' => 'Stel %s in',
        'reset' => 'resetten',
        'credential_deleted' => 'Referentie verwijderd!',
    ],
    'u2f' => [
        'header' => 'Passkey/MFA/2FA',
        'info' => 'Dit biedt alleen de mogelijkheid om WebAuthn te gebruiken om te authenticeren in plaats van gebruikersnaam en wachtwoord.',
        'empty' => 'Referentielijst is leeg!',
        'not_secure' => 'Omgeving niet beveiligd. U2F niet beschikbaar.',
        'new' => 'Nieuw apparaat registreren.',
        'credential_deleted' => 'Referentie verwijderd!',
        'credential_updated' => 'Referentie bijgewerkt!',
        'credential_registred' => 'Registratie succesvol!',
        '5_chars' => 'Minimaal 5 tekens.',
    ],
    'preferences' => [
        'header' => 'Voorkeuren',
        'save' => 'Voorkeur opslaan',
        'reset' => 'Resetten',
        'change_saved' => 'Voorkeur opgeslagen!',
    ],
    'shared_albums' => [
        'instruction' => 'Kies hoe gedeelde albums (albums van andere gebruikers) in uw galerij worden weergegeven:',
        'mode_default' => 'Standaardinstelling van server gebruiken',
        'mode_default_desc' => 'Neemt de standaard zichtbaarheidsmodus van de server over.',
        'mode_show' => 'Inline tonen',
        'mode_show_desc' => 'Gedeelde albums verschijnen onder uw eigen albums.',
        'mode_separate' => 'Aparte tabbladen',
        'mode_separate_desc' => 'Bekijk albums in aparte tabbladen "My Albums" en "Shared with Me".',
        'mode_separate_shared_only' => 'Alleen gedeeld',
        'mode_separate_shared_only_desc' => 'Aparte tabbladen die alleen rechtstreeks gedeelde albums tonen (openbare albums worden uitgesloten).',
        'mode_hide' => 'Verbergen',
        'mode_hide_desc' => 'Toon geen gedeelde albums.',
    ],
];
