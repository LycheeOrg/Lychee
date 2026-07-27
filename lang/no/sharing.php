<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sharing page
    |--------------------------------------------------------------------------
    */
    'title' => 'Deling',
    'info' => 'Denne siden gir en oversikt over, og muligheten til å redigere, delingsrettighetene knyttet til album.',
    'album_title' => 'Albumtittel',
    'username' => 'Brukernavn',
    'no_data' => 'Delingslisten er tom.',
    'share' => 'Del',
    'add_new_access_permission' => 'Legg til en ny tilgangstillatelse',
    'permission_deleted' => 'Tillatelse slettet!',
    'permission_created' => 'Tillatelse opprettet!',
    'propagate' => 'Spre',
    'propagate_help' => 'Spre gjeldende tilgangstillatelser til alle etterkommere<br>(underalbum og deres respektive underalbum osv.)',
    'propagate_default' => 'Som standard oppdateres eksisterende tillatelser (album-bruker)<br>og manglende legges til.<br>Andre tillatelser som ikke finnes i denne listen, blir ikke berørt.',
    'propagate_overwrite' => 'Overskriv eksisterende tillatelser i stedet for å oppdatere dem.<br>Dette vil også fjerne alle tillatelser som ikke finnes i denne listen.',
    'propagate_warning' => 'Denne handlingen kan ikke angres.',
    'permission_overwritten' => 'Spredning vellykket! Tillatelse overskrevet!',
    'permission_updated' => 'Spredning vellykket! Tillatelse oppdatert!',
    'bluk_share' => 'Massedeling',
    'bulk_share_instr' => 'Velg flere album og brukere å dele med.',
    'albums' => 'Album',
    'users' => 'Brukere',
    'no_users' => 'Ingen valgbare brukere.',
    'no_albums' => 'Ingen valgbare album.',
    'grants' => [
        'read' => 'Gir lesetilgang',
        'original' => 'Gir tilgang til originalbilde',
        'download' => 'Gir nedlasting',
        'upload' => 'Gir opplasting',
        'edit' => 'Gir redigering',
        'delete' => 'Gir sletting',
    ],
];
