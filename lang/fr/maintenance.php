<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Update Page
	|--------------------------------------------------------------------------
	*/
	'title' => 'Maintenance',
	'description' => 'Vous trouverez sur cette page toutes les actions necessaire au bon fonctionment de Lychee.',
	'cleaning' => [
		'title' => 'Nettoyer %s',
		'result' => '%s supprimé.',
		'description' => 'Supprimer le contenu de <span class="font-mono">%s</span>',
		'button' => 'Nettoyer',
	],
	'fix-tree' => [
		'title' => 'Statistique d’arbres',
		'Oddness' => 'Imparité',
		'Duplicates' => 'Duplicata',
		'Wrong parents' => 'Mauvais parents',
		'Missing parents' => 'Parents manquants',
		'button' => 'Fix tree',
	],
	'optimize' => [
		'title' => 'Optimisation de la base de donnée',
		'description' => 'Si vous remarquez que votre installation est devenue lente, il est possible que votre base de donnée n’ai pas les index requis.',
		'button' => 'Optimiser la base de donnée',
	],
	'update' => [
		'title' => 'Mises à jour',
		'check-button' => 'Vérifier les mise-à-jour',
		'update-button' => 'Mettre à jour',
		'no-pending-updates' => 'Aucune mise-à-jour disponible',
	],
];