<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

return [
	/*
	|--------------------------------------------------------------------------
	| Duplicate Finder Page
	|--------------------------------------------------------------------------
	*/
	'title' => 'Maintenance',
	'intro' => 'Sur cette page, vous trouverez les photos en double détectées dans votre base de données.',
	'found' => ' doublons trouvés !',
	'invalid-search' => ' Au moins une des conditions "checksum" ou "titre" doit être cochée.',
	'checksum-must-match' => 'Le checksum doit correspondre.',
	'title-must-match' => 'Le titre doit correspondre.',
	'must-be-in-same-album' => 'Doivent appartenir au même album.',

	'columns' => [
		'album' => 'Album',
		'photo' => 'Photo',
		'checksum' => 'Checksum',
	],

	'warning' => [
		'no-original-left' => 'Aucun original restant.',
		'keep-one' => 'Vous avez sélectionné tous les doublons de ce groupe. Veuillez en conserver au moins un.',
	],

	'delete-selected' => 'Supprimer la sélection',
];