<?php

return [
	/*
	|--------------------------------------------------------------------------
	| Import from Server
	|--------------------------------------------------------------------------
	*/
	'title' => 'Synchronize your server files',
	'description' => 'Synchronize your server files with Lychee. This will import photos from a directory and all children directories. This process is very slow and we recommend using workers and queues in order to avoid timeout.',
	'sync' => 'Synchronize',
	'loading' => 'Loading...',
	'selected_directory' => 'Current selected directory:',
	'resync_metadata' => "Re-sync metadata of existing files.",
	'delete_imported' => "Delete the original files.",
	'import_via_symlink' => "Import photos via symlink instead of copying the files.",
	'skip_duplicates' => "Skip photos and albums if they already exist in the gallery.",
	'delete_missing_photos' => "Delete photos in the album that are not present in the synced directory.",
	'delete_missing_albums' => "Delete albums in the parent album that are not present in the synced directory.",
	'importing_please_be_patient' => 'Importing, please be patient...',
];

