<?php
return [
    /**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */
    /*
	|--------------------------------------------------------------------------
	| Statistics page
	|--------------------------------------------------------------------------
	*/
    'title' => 'Statistiques',
    'preview_text' => 'Ceci est un aperçu de la page des statistiques disponible dans Lychee <span class="text-primary-emphasis font-bold">SE</span>.<br />Les données affichées ici sont générées aléatoirement et ne reflètent pas votre serveur.',
    'no_data' => 'L’utilisateur ne possède pas de données sur le serveur.',
    'collapse' => 'Réduire la taille des albums',
    'total' => [
        'total' => 'Total',
        'albums' => 'Albums',
        'photos' => 'Photos',
        'size' => 'Taille',
    ],
    'table' => [
        'username' => 'Propriétaire',
        'title' => 'Titre',
        'photos' => 'Photos',
        'descendants' => 'Enfants',
        'size' => 'Taille',
    ],
    'punch_card' => [
        'title' => 'Activité',
        'photo-taken' => '%d photos prises',
        'photo-taken-in' => '%d photos prises en %d',
        'photo-uploaded' => '%d photos téléversées',
        'photo-uploaded-in' => '%d photos téléversées en %d',
        'with-exif' => 'avec données EXIF',
        'less' => 'Moins',
        'more' => 'Plus',
        'tooltip' => '%d photos le %s',
        'created_at' => 'Date de téléversement',
        'taken_at' => 'Date EXIF',
        'caption' => 'Chaque colonne représente une semaine.',
    ],
    'metrics' => [
        'header' => 'Donnees en temps reel',
        'a_visitor' => 'Un visiteur',
        'visitors' => '%d visiteurs',
        'visit_singular' => '%1$s a vu %2$s',
        'favourite_singular' => '%1$s aime %2$s',
        'download_singular' => '%1$s a telecharge %2$s',
        'shared_singular' => '%1$s a partage %2$s',
        'visit_plural' => '%1$s ont vu %2$s',
        'favourite_plural' => '%1$s ont aime %2$s',
        'download_plural' => '%1$s on telecharge %2$s',
        'shared_plural' => '%1$s ont partage %2$s',
        'ago' => [
            'days' => 'il y a %d jours',
            'day' => 'il y a un jour',
            'hours' => 'il y a %d heures',
            'hour' => 'il y a une heure',
            'minutes' => 'il y a %d minutes',
            'few_minutes' => 'il y a quelques minutes',
            'seconds' => 'il y a quelques secondes',
        ],
    ],
];
