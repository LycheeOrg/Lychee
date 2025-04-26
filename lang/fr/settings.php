<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

return [
	/*
	|--------------------------------------------------------------------------
	| Settings page
	|--------------------------------------------------------------------------
	*/
	'title' => 'Paramètres',
	'small_screen' => 'Pour une meilleure expérience sur la page des paramètres,<br />nous vous recommandons d’utiliser un écran plus grand.',
	'tabs' => [
		'basic' => 'Basique',
		'all_settings' => 'Tous les paramètres',
	],
	'toasts' => [
		'change_saved' => 'Modification enregistrée !',
		'details' => 'Les paramètres ont été modifiés comme demandé',
		'error' => 'Erreur !',
		'error_load_css' => 'Impossible de charger dist/user.css',
		'error_load_js' => 'Impossible de charger dist/custom.js',
		'error_save_css' => 'Impossible d’enregistrer le CSS',
		'error_save_js' => 'Impossible d’enregistrer le JS',
		'thank_you' => 'Merci pour votre soutien.',
		'reload' => 'Rechargez la page pour bénéficier de toutes les fonctionnalités.',
	],
	'system' => [
		'header' => 'Système',
		'use_dark_mode' => 'Utiliser le mode sombre pour Lychee',
		'language' => 'Langue utilisée par Lychee',
		'nsfw_album_visibility' => 'Afficher les albums sensibles par défaut.',
		'nsfw_album_explanation' => 'Si l’album est public, il reste accessible, mais il est caché de l’interface<br />et <b>peut être révélé en appuyant sur <kbd>H</kbd></b>.',
		'cache_enabled' => 'Activer la mise en cache des réponses.',
		'cache_enabled_details' => 'Cela accélérera considérablement le temps de réponse de Lychee.<br><i class="pi pi-exclamation-triangle text-warning-600 mr-2"></i> Si vous utilisez des albums protégés par mot de passe, il est déconseillé d’activer cette option.',
	],
	'lychee_se' => [
		'header' => 'Lychee <span class="text-primary-emphasis">SE</span>',
		'call4action' => 'Bénéficiez de fonctionnalités exclusives et soutenez le développement de Lychee. Débloquez l’<a href="https://lycheeorg.dev/get-supporter-edition/" class="text-primary-500 underline">édition SE</a>.',
		'preview' => 'Activer l’aperçu des fonctionnalités de Lychee SE',
		'hide_call4action' => 'Masquer ce formulaire d’enregistrement pour Lychee SE. Je suis satisfait de Lychee tel quel :)',
		'hide_warning' => 'Si activé, la seule façon d’enregistrer votre clé de licence sera via l’onglet "Plus" ci-dessus. Les changements seront appliqués après rechargement de la page.',
	],
	'dropbox' => [
		'header' => 'Dropbox',
		'instruction' => 'Pour importer des photos depuis votre compte Dropbox, vous devez obtenir une clé d’application valide depuis leur site.',
		'api_key' => 'Clé API Dropbox',
		'set_key' => 'Définir la clé Dropbox',
	],
	'gallery' => [
		'header' => 'Galerie',
		'photo_order_column' => 'Colonne par défaut pour le tri des photos',
		'photo_order_direction' => 'Ordre par défaut pour le tri des photos',
		'album_order_column' => 'Colonne par défaut pour le tri des albums',
		'album_order_direction' => 'Ordre par défaut pour le tri des albums',
		'aspect_ratio' => 'Ratio d’aspect par défaut des miniatures d’albums',
		'photo_layout' => 'Disposition des photos',
		'album_decoration' => 'Afficher les décorations sur la couverture des albums (nombre de sous-albums ou de photos)',
		'album_decoration_direction' => 'Aligner les décorations d’album horizontalement ou verticalement',
		'photo_overlay' => 'Superposition d’information par défaut sur les images',
		'license_default' => 'Licence par défaut utilisée pour les albums',
		'license_help' => 'Besoin d’aide pour choisir ?',
	],
	'geolocation' => [
		'header' => 'Géolocalisation',
		'map_display' => 'Afficher la carte selon les coordonnées GPS',
		'map_display_public' => 'Permettre aux utilisateurs anonymes d’accéder à la carte',
		'map_provider' => 'Définit le fournisseur de carte',
		'map_include_subalbums' => 'Inclure les photos des sous-albums sur la carte',
		'location_decoding' => 'Utiliser le décodage de position GPS',
		'location_show' => 'Afficher la localisation extraite des coordonnées GPS',
		'location_show_public' => 'Les utilisateurs anonymes peuvent accéder à la localisation extraite',
	],
	'cssjs' => [
		'header' => 'CSS & JS personnalisés',
		'change_css' => 'Modifier le CSS',
		'change_js' => 'Modifier le JS',
	],
	'all' => [
		'old_setting_style' => 'Ancien style des paramètres',
		'expert_settings' => 'Mode expert',
		'change_detected' => 'Des paramètres ont été modifiés.',
		'save' => 'Enregistrer',
		'back_to_settings' => 'Retour aux paramètres groupés',
	],

	'tool_option' => [
		'disabled' => 'désactivé',
		'enabled' => 'activé',
		'discover' => 'découvrir',
	],

	'groups' => [
		'general' => 'Général',
		'system' => 'Système',
		'modules' => 'Modules',
		'advanced' => 'Avancé',
	],
];