<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Sharing page
    |--------------------------------------------------------------------------
    */
    'title' => 'Delning',
    'info' => 'Denna sida ger en översikt över och möjlighet att redigera delningsrättigheterna för album.',
    'album_title' => 'Albumtitel',
    'username' => 'Användarnamn',
    'no_data' => 'Delningslistan är tom.',
    'filter_placeholder' => 'Sök på albumtitel...',
    'hide_empty' => 'Dölj album utan delningar',
    'screen_too_small' => 'Denna sida kräver en större skärm.',
    'share' => 'Dela',
    'add_new_access_permission' => 'Lägg till en ny åtkomstbehörighet',
    'permission_deleted' => 'Behörighet raderad!',
    'permission_created' => 'Behörighet skapad!',
    'propagate' => 'Sprid',
    'propagate_help' => 'Sprid de aktuella åtkomstbehörigheterna till alla underliggande album<br>(underalbum och deras respektive underalbum osv.)',
    'propagate_default' => 'Som standard uppdateras befintliga behörigheter (album-användare)<br>och de saknade läggs till.<br>Ytterligare behörigheter som inte finns i denna lista lämnas orörda.',
    'propagate_overwrite' => 'Skriv över befintliga behörigheter i stället för att uppdatera dem.<br>Detta tar även bort alla behörigheter som inte finns i denna lista.',
    'propagate_warning' => 'Denna åtgärd kan inte ångras.',
    'permission_overwritten' => 'Spridningen lyckades! Behörigheter överskrivna!',
    'permission_updated' => 'Spridningen lyckades! Behörigheter uppdaterade!',
    'bluk_share' => 'Massdelning',
    'bulk_share_instr' => 'Välj flera album och användare att dela med.',
    'albums' => 'Album',
    'users' => 'Användare',
    'no_users' => 'Inga valbara användare.',
    'no_albums' => 'Inga valbara album.',
    'grants' => [
        'read' => 'Ger läsåtkomst',
        'original' => 'Ger åtkomst till originalfoto',
        'download' => 'Tillåter nedladdning',
        'upload' => 'Tillåter uppladdning',
        'edit' => 'Tillåter redigering',
        'move' => 'Tillåter flytt, kopiering och sammanslagning',
        'delete' => 'Tillåter radering',
    ],
];
