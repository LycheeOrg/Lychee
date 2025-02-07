<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

return [
	/*
	|--------------------------------------------------------------------------
	| Sharing page
	|--------------------------------------------------------------------------
	*/
	'title' => 'Sharing',

	'info' => 'This page gives an overview of and the ability to edit the sharing rights associated with albums.',
	'album_title' => 'Album title',
	'username' => 'Username',
	'no_data' => 'Sharing list is empty.',
	'share' => 'Share',
	'add_new_access_permission' => 'Add a new access permission',
	'permission_deleted' => 'Permission deleted!',
	'permission_created' => 'Permission created!',
	'propagate' => 'Propagate',

	'propagate_help' => 'Propagate the current access permissions to all descendants<br>(sub-albums and their respective sub-albums etc)',
	'propagate_default' => 'By default, existing permissions (album-user)<br>are updated and the missing ones added.<br>Additional permissions not present in this list are left untouched.',
	'propagate_overwrite' => 'Overwrite the existing permissions instead of updating.<br>This will also remove all permissions not present in this list.',
	'propagate_warning' => 'This action cannot be undone.',

	'permission_overwritten' => 'Propagation successful! Permission overwritten!',
	'permission_updated' => 'Propagation successful! Permission updated!',

	'grants' => [
		'read' => 'Grants read access',
		'original' => 'Grants access to original photo',
		'download' => 'Grants download',
		'upload' => 'Grants upload',
		'edit' => 'Grants edit',
		'delete' => 'Grants delete',
	],
];