<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

return [
	/*
	|--------------------------------------------------------------------------
	| Dialogs
	|--------------------------------------------------------------------------
	*/
	'button' => [
		'close' => 'Fermer',
		'cancel' => 'Annuler',
		'save' => 'Enregistrer',
		'delete' => 'Supprimer',
		'move' => 'Déplacer',
	],
	'about' => [
		'subtitle' => 'Gestion de photos auto-hébergée, comme il se doit',
		'description' => 'Lychee est un outil gratuit de gestion de photos, qui fonctionne sur votre propre serveur ou hébergement web. L’installation ne prend que quelques secondes. Téléversez, gérez et partagez vos photos comme avec une application native. Lychee fournit tout ce dont vous avez besoin, et vos photos sont stockées en toute sécurité.',
		'update_available' => 'Mise à jour disponible !',
		'thank_you' => 'Merci pour votre soutien !',
		'get_supporter_or_register' => 'Bénéficiez de fonctionnalités exclusives et soutenez le développement de Lychee.<br />Débloquez l’<a href="https://lycheeorg.dev/get-supporter-edition/" class="text-primary-500 underline">édition Supporter</a> ou enregistrez votre clé de licence',
		'here' => 'ici',
	],
	'dropbox' => [
		'not_configured' => 'Dropbox n’est pas configuré.',
	],
	'import_from_link' => [
		'instructions' => 'Veuillez entrer le lien direct d’une photo à importer :',
		'import' => 'Importer',
	],
	'keybindings' => [
		'header' => 'Raccourcis clavier',
		'don_t_show_again' => 'Ne plus afficher',
		'hide_header_button' => 'Don\'t show help in header',
		'side_wide' => 'Raccourcis globaux',
		'back_cancel' => 'Retour/Annuler',
		'confirm' => 'Confirmer',
		'login' => 'Connexion',
		'toggle_full_screen' => 'Basculer en plein écran',
		'toggle_sensitive_albums' => 'Afficher/masquer les albums sensibles',

		'albums' => 'Raccourcis pour les albums',
		'new_album' => 'Nouvel album',
		'upload_photos' => 'Téléverser des photos',
		'search' => 'Rechercher',
		'show_this_modal' => 'Afficher cette fenêtre',
		'select_all' => 'Tout sélectionner',
		'move_selection' => 'Déplacer la sélection',
		'delete_selection' => 'Supprimer la sélection',

		'album' => 'Raccourcis album',
		'slideshow' => 'Démarrer/Arrêter le diaporama',
		'toggle' => 'Afficher/masquer le panneau',

		'photo' => 'Raccourcis photo',
		'previous' => 'Photo précédente',
		'next' => 'Photo suivante',
		'cycle' => 'Changer le mode d’affichage',
		'star' => 'Ajouter aux favoris',
		'move' => 'Déplacer la photo',
		'delete' => 'Supprimer la photo',
		'edit' => 'Modifier les informations',
		'show_hide_meta' => 'Afficher les informations',

		'keep_hidden' => 'Nous la garderons cachée.',
		'button_hidden' => 'We will hide the button in the header.',
	],
	'login' => [
		'username' => 'Nom d’utilisateur',
		'password' => 'Mot de passe',
		'unknown_invalid' => 'Utilisateur inconnu ou mot de passe invalide.',
		'signin' => 'Connexion',
	],
	'register' => [
		'enter_license' => 'Entrez votre clé de licence ci-dessous :',
		'license_key' => 'Clé de licence',
		'invalid_license' => 'Clé de licence invalide.',
		'register' => 'Enregistrer',
	],
	'share_album' => [
		'url_copied' => 'URL copiée dans le presse-papiers !',
	],
	'upload' => [
		'completed' => 'Terminé',
		'uploaded' => 'Téléversé :',
		'release' => 'Relâchez le fichier pour le téléverser !',
		'select' => 'Cliquez ici pour sélectionner les fichiers à téléverser',
		'drag' => '(Ou glissez les fichiers sur la page)',
		'loading' => 'Chargement',
		'resume' => 'Reprendre',
		'uploading' => 'Téléversement',
		'finished' => 'Terminé',
		'failed_error' => 'Échec du téléversement. Le serveur a retourné une erreur !',
	],
	'visibility' => [
		'public' => 'Public',
		'public_expl' => 'Les utilisateurs anonymes peuvent accéder à cet album, sous réserve des restrictions ci-dessous.',
		'full' => 'Original',
		'full_expl' => 'Les utilisateurs anonymes peuvent voir les photos en pleine résolution.',
		'hidden' => 'Caché',
		'hidden_expl' => 'Les utilisateurs anonymes ont besoin d’un lien direct pour accéder à cet album.',
		'downloadable' => 'Téléchargeable',
		'downloadable_expl' => 'Les utilisateurs anonymes peuvent télécharger cet album.',
		'upload' => 'Autoriser les téléversements',
		'upload_expl' => '<i class="pi pi-exclamation-triangle text-warning-700 mr-1"></i> Les utilisateurs anonymes peuvent téléverser des photos dans cet album.',
		'password' => 'Mot de passe',
		'password_prot' => 'Protégé par mot de passe',
		'password_prot_expl' => 'Les utilisateurs anonymes doivent connaître le mot de passe pour accéder à cet album.',
		'password_prop_not_compatible' => 'Le cache de réponse entre en conflit avec ce paramètre.<br>En raison du cache, le déverrouillage de cet album<br>révèlera aussi son contenu à d’autres utilisateurs anonymes.',
		'nsfw' => 'Sensible',
		'nsfw_expl' => 'L’album contient du contenu sensible.',
		'visibility_updated' => 'Visibilité mise à jour.',
	],
	'move_album' => [
		'confirm_single' => 'Êtes-vous sûr de vouloir déplacer l’album « %1$s » dans l’album « %2$s » ?',
		'confirm_multiple' => 'Êtes-vous sûr de vouloir déplacer tous les albums sélectionnés dans l’album « %s » ?',
		'move_single' => 'Déplacer l’album',
		'move_to' => 'Déplacer vers',
		'move_to_single' => 'Déplacer %s vers :',
		'move_to_multiple' => 'Déplacer %d albums vers :',
		'no_album_target' => 'Aucun album vers lequel déplacer',
		'moved_single' => 'Album déplacé !',
		'moved_single_details' => '%1$s déplacé vers %2$s',
		'moved_details' => 'Album(s) déplacé(s) vers %s',
	],
	'new_album' => [
		'menu' => 'Créer un album',
		'info' => 'Entrez un titre pour le nouvel album :',
		'title' => 'titre',
		'create' => 'Créer l’album',
	],
	'new_tag_album' => [
		'menu' => 'Créer un album par étiquette',
		'info' => 'Entrez un titre pour le nouvel album par étiquette :',
		'title' => 'titre',
		'set_tags' => 'Définir les étiquettes à afficher',
		'warn' => 'Appuyez sur entrée après chaque étiquette',
		'create' => 'Créer l’album par étiquette',
	],
	'delete_album' => [
		'confirmation' => 'Êtes-vous sûr de vouloir supprimer l’album « %s » et toutes les photos qu’il contient ?',
		'confirmation_multiple' => 'Êtes-vous sûr de vouloir supprimer les %d albums sélectionnés et toutes les photos qu’ils contiennent ?',
		'warning' => 'Cette action est irréversible !',
		'delete' => 'Supprimer l’album et les photos',
	],
	'transfer' => [
		'query' => 'Transférer la propriété de l’album vers',
		'confirmation' => 'Êtes-vous sûr de vouloir transférer la propriété de l’album « %s » et de toutes les photos qu’il contient à « %s » ?',
		'lost_access_warning' => 'Vous perdrez l’accès à cet album.',
		'warning' => 'Cette action est irréversible !',
		'transfer' => 'Transférer la propriété de l’album et des photos',
	],
	'rename' => [
		'photo' => 'Entrez un nouveau titre pour cette photo :',
		'album' => 'Entrez un nouveau titre pour cet album :',
		'rename' => 'Renommer',
	],
	'merge' => [
		'merge_to' => 'Fusionner %s avec :',
		'merge_to_multiple' => 'Fusionner %d albums avec :',
		'no_albums' => 'Aucun album avec lequel fusionner.',
		'confirm' => 'Êtes-vous sûr de vouloir fusionner l’album « %1$s » avec l’album « %2$s » ?',
		'confirm_multiple' => 'Êtes-vous sûr de vouloir fusionner tous les albums sélectionnés avec l’album « %s » ?',
		'merge' => 'Fusionner les albums',
		'merged' => 'Album(s) fusionné(s) dans %s !',
	],
	'unlock' => [
		'password_required' => 'Cet album est protégé par un mot de passe. Entrez-le ci-dessous pour voir les photos :',
		'password' => 'Mot de passe',
		'unlock' => 'Déverrouiller',
	],
	'photo_tags' => [
		'question' => 'Entrez les étiquettes pour cette photo.',
		'question_multiple' => 'Entrez les étiquettes pour les %d photos sélectionnées. Les étiquettes existantes seront écrasées.',
		'no_tags' => 'Aucune étiquette',
		'set_tags' => 'Définir les étiquettes',
		'updated' => 'Étiquettes mises à jour !',
		'tags_override_info' => 'Si cette case n’est pas cochée, les étiquettes seront ajoutées à celles existantes.',
	],
	'photo_copy' => [
		'no_albums' => 'Aucun album vers lequel copier',
		'copy_to' => 'Copier %s vers :',
		'copy_to_multiple' => 'Copier %d photos vers :',
		'confirm' => 'Copier %s vers %s.',
		'confirm_multiple' => 'Copier %d photos vers %s.',
		'copy' => 'Copier',
		'copied' => 'Photo(s) copiée(s) !',
	],
	'photo_delete' => [
		'confirm' => 'Êtes-vous sûr de vouloir supprimer la photo « %s » ?',
		'confirm_multiple' => 'Êtes-vous sûr de vouloir supprimer les %d photos sélectionnées ?',
		'deleted' => 'Photo(s) supprimée(s) !',
	],
	'move_photo' => [
		'move_single' => 'Déplacer %s vers :',
		'move_multiple' => 'Déplacer %d photos vers :',
		'confirm' => 'Déplacer %s vers %s.',
		'confirm_multiple' => 'Déplacer %d photos vers %s.',
		'moved' => 'Photo(s) déplacée(s) vers %s !',
	],
	'target_user' => [
		'placeholder' => 'Sélectionner un utilisateur',
	],
	'target_album' => [
		'placeholder' => 'Sélectionner un album',
	],
	'webauthn' => [
		'u2f' => 'U2F',
		'success' => 'Authentification réussie !',
		'error' => 'Oups, une erreur s’est produite. Veuillez recharger la page et réessayer !',
	],
	'se' => [
		'available' => 'Disponible dans l’édition Supporter',
	],
	'session_expired' => [
		'title' => 'Session expirée',
		'message' => 'Votre session a expiré.<br />Veuillez recharger la page.',
		'reload' => 'Recharger',
		'go_to_gallery' => 'Aller à la galerie',
	],
];