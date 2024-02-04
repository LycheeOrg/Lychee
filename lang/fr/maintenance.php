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
	'fix-jobs' => [
		'title' => 'Réparer l’historique des Jobs',
		'description' => 'Marquer les jobs avec status <span class="text-ready-400">%s</span> ou <span class="text-primary-500">%s</span> comme <span class="text-danger-700">%s</span>.',
		'button' => 'Réparer l’historique',
	],
	'gen-sizevariants' => [
		'title' => '%s manquants',
		'description' => 'Nous avons trouvé %d %s qui peuvent être générés.',
		'button' => 'Généré!',
		'success' => 'Nous avons créé %d %s avec succès.',
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