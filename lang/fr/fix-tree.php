<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

return [
	/*
	|--------------------------------------------------------------------------
	| Fix-tree Page
	|--------------------------------------------------------------------------
	*/
	'title' => 'Maintenance',
	'intro' => 'Cette page vous permet de réorganiser et corriger manuellement vos albums.<br />Avant toute modification, nous vous recommandons fortement de lire à propos des structures d’arbre en « Nested Set ».',
	'warning' => 'Vous pouvez réellement casser votre installation Lychee ici, modifiez les valeurs à vos risques et périls.',

	'help' => [
		'header' => 'Aide',
		'hover' => 'Survolez les identifiants ou titres pour mettre en surbrillance les albums associés.',
		'left' => '<span class="text-muted-color-emphasis font-bold">Gauche</span>',
		'right' => '<span class="text-muted-color-emphasis font-bold">Droite</span>',
		'convenience' => 'Pour votre confort, les boutons <i class="pi pi-angle-up"></i> et <i class="pi pi-angle-down"></i> vous permettent de modifier les valeurs de %s et %s respectivement de +1 et -1, avec propagation.',
		'left-right-warn' => 'Les icônes <i class="text-warning-600 pi pi-chevron-circle-left"></i> et <i class="text-warning-600 pi pi-chevron-circle-right"></i> indiquent que la valeur de %s (et respectivement %s) est dupliquée quelque part.',
		'parent-marked' => 'Un <span class="font-bold text-danger-600">identifiant parent</span> en rouge indique que les valeurs %s et %s ne respectent pas la structure en arbre « Nested Set ». Modifiez soit l’<span class="font-bold text-danger-600">identifiant parent</span>, soit les valeurs %s/%s.',
		'slowness' => 'Cette page peut être lente si vous avez un grand nombre d’albums.',
	],

	'buttons' => [
		'reset' => 'Réinitialiser',
		'check' => 'Vérifier',
		'apply' => 'Appliquer',
	],

	'table' => [
		'title' => 'Titre',
		'left' => 'Gauche',
		'right' => 'Droite',
		'id' => 'Id',
		'parent' => 'Id Parent',
	],

	'errors' => [
		'invalid' => 'Arborescence invalide !',
		'invalid_details' => 'Aucune application possible, car cela mènerait à un état cassé garanti.',
		'invalid_left' => 'L’album %s a une valeur gauche invalide.',
		'invalid_right' => 'L’album %s a une valeur droite invalide.',
		'invalid_left_right' => 'L’album %s a des valeurs gauche/droite invalides. La valeur gauche doit être strictement inférieure à droite : %s < %s.',
		'duplicate_left' => 'L’album %s a une valeur gauche dupliquée : %s.',
		'duplicate_right' => 'L’album %s a une valeur droite dupliquée : %s.',
		'parent' => 'L’album %s a un identifiant parent inattendu : %s.',
		'unknown' => 'L’album %s a une erreur inconnue.',
	],
];