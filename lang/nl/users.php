<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Users page
    |--------------------------------------------------------------------------
    */
    'title' => 'Gebruikers',
    'description' => 'Hier kunt u de gebruikers van uw Lychee-installatie beheren. U kunt gebruikers aanmaken, bewerken en verwijderen.',
    'create' => 'Maak een nieuwe gebruiker aan',
    'username' => 'Gebruikersnaam',
    'password' => 'Wachtwoord',
    'legend' => 'Legenda',
    'upload_rights' => 'Wanneer geselecteerd, kan de gebruiker inhoud uploaden.',
    'edit_rights' => 'Wanneer geselecteerd, kan de gebruiker zijn profiel wijzigen (gebruikersnaam, wachtwoord).',
    'upload_trust_level' => 'Vertrouwensniveau voor uploads — bepaalt of geüploade inhoud direct openbaar is.',

    'quota' => 'Wanneer ingesteld, heeft de gebruiker een ruimtequotum voor foto’s (in kB).',
    'user_deleted' => 'Gebruiker verwijderd',
    'user_created' => 'Gebruiker aangemaakt',
    'user_updated' => 'Gebruiker bijgewerkt',
    'change_saved' => 'Wijziging opgeslagen!',
    'create_edit' => [
        'upload_rights' => 'Gebruiker kan inhoud uploaden.',
        'edit_rights' => 'Gebruiker kan zijn profiel wijzigen (gebruikersnaam, wachtwoord).',
        'admin_rights' => 'Gebruiker heeft beheerdersrechten.',
        'upload_trust_level' => 'Vertrouwensniveau voor uploads',
        'upload_trust_level_check' => 'Controle – geüploade inhoud vereist goedkeuring van een beheerder voordat deze openbaar wordt.',
        'upload_trust_level_monitor' => 'Monitor – geüploade inhoud is openbaar, tenzij deze wordt gemarkeerd vanwege de inhoud.',
        'upload_trust_level_trust_but_verify' => 'Vertrouwen maar verifiëren – geüploade inhoud is openbaar; bevindingen van het type “beoordelen” worden automatisch goedgekeurd, bevindingen van het type “blokkeren” zijn configureerbaar.',
        'upload_trust_level_trusted' => 'Vertrouwd – geüploade inhoud is direct openbaar.',
        'trust_level_options' => [
            'trusted' => 'Vertrouwd',
            'trust_but_verify' => 'Vertrouwen maar verifiëren',
            'monitor' => 'Monitor',
            'check' => 'Controle',
        ],
        'quota' => 'Gebruiker heeft een quotumlimiet.',
        'quota_kb' => 'quotum in kB (0 voor standaard)',
        'note' => 'Beheerdersnotitie (niet openbaar zichtbaar)',
        'create' => 'Aanmaken',
        'edit' => 'Bewerken',
    ],
    'invite' => [
        'button' => 'Nodig een gebruiker uit',
        'links_are_not_revokable' => 'Uitnodigingslinks zijn niet herroepbaar.',
        'link_is_valid_x_days' => 'Deze link is %d dagen geldig.',
    ],
    'line' => [
        'owner' => 'Eigenaar',
        'admin' => 'Beheerder',
        'edit' => 'Bewerken',
        'delete' => 'Verwijderen',
    ],
];
