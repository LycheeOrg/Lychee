<?php
return [
    /*
	|--------------------------------------------------------------------------
	| Sharing page
	|--------------------------------------------------------------------------
	*/
    'title' => 'Delen',
    'info' => 'Deze pagina geeft een overzicht van en de mogelijkheid om de delingsrechten die aan albums zijn gekoppeld te bewerken.',
    'album_title' => 'Albumtitel',
    'username' => 'Gebruikersnaam',
    'no_data' => 'De delingslijst is leeg.',
    'share' => 'Delen',
    'add_new_access_permission' => 'Voeg een nieuwe toegangsrechten toe',
    'permission_deleted' => 'Toestemming verwijderd!',
    'permission_created' => 'Toestemming aangemaakt!',
    'propagate' => 'Propageren',
    'propagate_help' => 'Propageren van de huidige toegangsrechten naar alle afstammelingen<br>(subalbums en hun respectieve subalbums, enz.)',
    'propagate_default' => 'Standaard worden bestaande rechten (album-gebruiker)<br>bijgewerkt en de ontbrekende toegevoegd.<br>Aanvullende rechten die niet in deze lijst staan, blijven onaangeroerd.',
    'propagate_overwrite' => 'Overschrijf de bestaande rechten in plaats van bij te werken.<br>Dit verwijdert ook alle rechten die niet in deze lijst staan.',
    'propagate_warning' => 'Deze actie kan niet ongedaan worden gemaakt.',
    'permission_overwritten' => 'Propageren succesvol! Toestemming overschreven!',
    'permission_updated' => 'Propageren succesvol! Toestemming bijgewerkt!',
    'bluk_share' => 'Bulk delen',
    'bulk_share_instr' => 'Selecteer meerdere albums en gebruikers om mee te delen.',
    'albums' => 'Albums',
    'users' => 'Gebruikers',
    'no_users' => 'Geen selecteerbare gebruikers.',
    'no_albums' => 'Geen selecteerbare albums.',
    'grants' => [
        'read' => 'Geeft leesrechten',
        'original' => 'Geeft toegang tot originele foto',
        'download' => 'Geeft downloadrechten',
        'upload' => 'Geeft uploadrechten',
        'edit' => 'Geeft bewerkingsrechten',
        'delete' => 'Geeft verwijderrechten',
    ],
];
