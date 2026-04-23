<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Users page
    |--------------------------------------------------------------------------
    */
    'title' => 'Brukere',
    'description' => 'Her kan du administrere brukerne av Lychee-installasjonen din. Du kan opprette, redigere og slette brukere.',
    'create' => 'Lag ny bruker',
    'username' => 'Brukernavn',
    'password' => 'Passord',
    'legend' => 'Veilder',
    'upload_rights' => 'Når dette er valgt, kan brukeren laste opp innhold.',
    'edit_rights' => 'Når dette er valgt, kan brukeren endre profilen sin (brukernavn, passord).',
    'upload_trust_level' => 'Tillitsnivå for opplasting – styrer om opplastinger er offentlige umiddelbart.',
    'quota' => 'Når dette er angitt, har brukeren en diskplasskvote for bilder (i kB).',
    'user_deleted' => 'Brukeren ble slettet',
    'user_created' => 'Brukeren ble laget',
    'user_updated' => 'Brukeren ble oppdatert',
    'change_saved' => 'Endringen er lagret!',
    'create_edit' => [
        'upload_rights' => 'Brukeren kan laste opp innhold.',
        'edit_rights' => 'Brukeren kan endre profilen sin (brukernavn, passord).',
        'admin_rights' => 'Brukeren har administratorrettigheter.',
        'upload_trust_level' => 'Last opp tillitsnivå',
        'upload_trust_level_check' => 'Kryss av – opplastinger krever godkjenning fra administrator før de blir offentlige.',
        'upload_trust_level_monitor' => 'Overvåk – opplastinger er offentlige med mindre de er flagget for innhold.',
        'upload_trust_level_trusted' => 'Pålitelig – opplastinger blir umiddelbart offentlige.',
        'quota' => 'Brukeren har en kvotegrense.',
        'quota_kb' => 'kvote i kB (0 for standard)',
        'note' => 'Adminnotat (ikke offentlig synlig)',
        'create' => 'Lage',
        'edit' => 'Redigere',
    ],
    'invite' => [
        'button' => 'Inviter bruker',
        'links_are_not_revokable' => 'Invitasjonslenker kan ikke tilbakekalles.',
        'link_is_valid_x_days' => 'Denne lenken er gyldig i %d dager.',
    ],
    'line' => [
        'owner' => 'Eier',
        'admin' => 'Adminbruker',
        'edit' => 'Endre',
        'delete' => 'Slett',
    ],
];
