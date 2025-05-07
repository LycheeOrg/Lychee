<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Update Page
	|--------------------------------------------------------------------------
	*/
	'title' => 'Maintenance',
	'description' => 'Sur cette page, vous trouverez toutes les actions nécessaires pour que votre installation de Lychee fonctionne de manière fluide et efficace.',
	'cleaning' => [
		'title' => 'Nettoyage de %s',
		'result' => '%s supprimé.',
		'description' => 'Supprimer tout le contenu de <span class="font-mono">%s</span>',
		'button' => 'Nettoyer',
	],
	'duplicate-finder' => [
		'title' => 'Doublons',
		'description' => 'Ce module recense les doublons potentiels entre les photos.',
		'duplicates-all' => 'Doublons sur tous les albums',
		'duplicates-title' => 'Doublons de titre par album',
		'duplicates-per-album' => 'Doublons par album',
		'show' => 'Afficher les doublons',
	],
	'fix-jobs' => [
		'title' => 'Correction de l’historique des tâches',
		'description' => 'Marquer les tâches avec le statut <span class="text-ready-400">%s</span> ou <span class="text-primary-500">%s</span> comme <span class="text-danger-700">%s</span>.',
		'button' => 'Corriger l’historique',
	],
	'gen-sizevariants' => [
		'title' => 'Manquants : %s',
		'description' => '%d %s pouvant être générés ont été trouvés.',
		'button' => 'Générer !',
		'success' => '%d %s générés avec succès.',
	],
	'fill-filesize-sizevariants' => [
		'title' => 'Tailles de fichiers manquantes',
		'description' => '%d variantes de petite taille sans information de taille de fichier ont été trouvées.',
		'button' => 'Récupérer les données !',
		'success' => 'Taille des fichiers calculée avec succès pour %d variantes.',
	],
	'fix-tree' => [
		'title' => 'Statistiques de l’arborescence',
		'Oddness' => 'Anomalies',
		'Duplicates' => 'Doublons',
		'Wrong parents' => 'Parents incorrects',
		'Missing parents' => 'Parents manquants',
		'button' => 'Corriger l’arborescence',
	],
	'optimize' => [
		'title' => 'Optimiser la base de données',
		'description' => 'Si vous constatez un ralentissement de votre installation, cela peut venir d’un manque d’index dans votre base de données.',
		'button' => 'Optimiser la base',
	],
	'update' => [
		'title' => 'Mises à jour',
		'check-button' => 'Vérifier les mises à jour',
		'update-button' => 'Mettre à jour',
		'no-pending-updates' => 'Aucune mise à jour en attente.',
	],
	'flush-cache' => [
		'title' => 'Vider le cache',
		'description' => 'Vider le cache de tous les utilisateurs pour résoudre les problèmes d’invalidation.',
		'button' => 'Vider',
	],
];
