<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

return [
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
		'header' => 'Live metrics',
		'a_visitor' => 'A visitor',
		'visitors' => '%d visitors',
		'visit_singular' => '%1$s viewed %2$s',
		'favourite_singular' => '%1$s favourited %2$s',
		'download_singular' => '%1$s downloaded %2$s',
		'shared_singular' => '%1$s shared %2$s',
		'visit_plural' => '%1$s viewed %2$s',
		'favourite_plural' => '%1$s favourited %2$s',
		'download_plural' => '%1$s downloaded %2$s',
		'shared_plural' => '%1$s shared %2$s',
		'ago' => [
			'days' => '%d days ago',
			'day' => 'a day ago',
			'hours' => '%d hours ago',
			'hour' => 'an hour ago',
			'minutes' => '%d minutes ago',
			'few_minutes' => 'a few minute ago',
			'seconds' => 'a few seconds ago',
		],
	],
];