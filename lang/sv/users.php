<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Users page
    |--------------------------------------------------------------------------
    */
    'title' => 'Användare',
    'description' => 'Här kan du hantera användarna i din Lychee-installation. Du kan skapa, redigera och radera användare.',
    'create' => 'Skapa en ny användare',
    'username' => 'Användarnamn',
    'password' => 'Lösenord',
    'legend' => 'Förklaring',
    'upload_rights' => 'När markerat kan användaren ladda upp innehåll.',
    'edit_rights' => 'När markerat kan användaren ändra sin profil (användarnamn, lösenord).',
    'upload_trust_level' => 'Förtroendenivå för uppladdning — styr om uppladdningar blir offentliga direkt.',

    'quota' => 'När angivet har användaren en lagringskvot för bilder (i kB).',
    'user_deleted' => 'Användare raderad',
    'user_created' => 'Användare skapad',
    'user_updated' => 'Användare uppdaterad',
    'change_saved' => 'Ändringen har sparats!',
    'create_edit' => [
        'upload_rights' => 'Användaren kan ladda upp innehåll.',
        'edit_rights' => 'Användaren kan ändra sin profil (användarnamn, lösenord).',
        'admin_rights' => 'Användaren har administratörsrättigheter.',
        'upload_trust_level' => 'Förtroendenivå för uppladdning',
        'upload_trust_level_check' => 'Kontrollera – uppladdningar måste godkännas av en administratör innan de blir offentliga.',
        'upload_trust_level_monitor' => 'Övervaka – uppladdningar är offentliga om de inte flaggas för sitt innehåll.',
        'upload_trust_level_trust_but_verify' => 'Lita men verifiera – uppladdningar är offentliga; granskningsfynd godkänns automatiskt, blockeringsfynd är konfigurerbara.',
        'upload_trust_level_trusted' => 'Betrodd – uppladdningar blir offentliga direkt.',
        'trust_level_options' => [
            'trusted' => 'Betrodd',
            'trust_but_verify' => 'Lita men verifiera',
            'monitor' => 'Övervaka',
            'check' => 'Kontrollera',
        ],
        'quota' => 'Användaren har en kvotgräns.',
        'quota_kb' => 'kvot i kB (0 för standard)',
        'note' => 'Administratörsanteckning (inte offentligt synlig)',
        'create' => 'Skapa',
        'edit' => 'Redigera',
    ],
    'invite' => [
        'button' => 'Bjud in användare',
        'links_are_not_revokable' => 'Invitation links are not revokable.',
        'link_is_valid_x_days' => 'Denna länk är giltig i %d dagar.',
    ],
    'line' => [
        'owner' => 'Ägare',
        'admin' => 'Administratör',
        'edit' => 'Redigera',
        'delete' => 'Radera',
    ],
];
