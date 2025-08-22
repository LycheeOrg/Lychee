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
    'legend' => 'Legend',
    'upload_rights' => 'Når dette er valgt, kan brukeren laste opp innhold.',
    'edit_rights' => 'Når dette er valgt, kan brukeren endre profilen sin (brukernavn, passord).',
    'quota' => 'Når dette er angitt, har brukeren en diskplasskvote for bilder (i kB).',
    'user_deleted' => 'Brukeren ble slettet',
    'user_created' => 'Brukeren ble laget',
    'user_updated' => 'Brukeren ble oppdatert',
    'change_saved' => 'Endringen er lagret!',
    'create_edit' => [
        'upload_rights' => 'Brukeren kan laste opp innhold.',
        'edit_rights' => 'Brukeren kan endre profilen sin (brukernavn, passord).',
        'admin_rights' => 'Brukeren har administratorrettigheter.',
        'quota' => 'Brukeren har en kvotegrense.',
        'quota_kb' => 'kvote i kB (0 for standard)',
        'note' => 'Admin note (not publically visible)',
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
