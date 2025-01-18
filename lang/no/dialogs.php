<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

return [
	/*
	|--------------------------------------------------------------------------
	| Dialogs
	|--------------------------------------------------------------------------
	*/
	'button' => [
		'close' => 'Close',
		'cancel' => 'Cancel',
		'save' => 'Save',
		'delete' => 'Delete',
		'move' => 'Move',
	],
	'about' => [
		'subtitle' => 'Self-hosted photo-management done right',
		'description' => 'Lychee is a free photo-management tool, which runs on your server or web-space. Installing is a matter of seconds. Upload, manage and share photos like from a native application. Lychee comes with everything you need and all your photos are stored securely.',
		'update_available' => 'Update available!',
		'thank_you' => 'Thank you for your support!',
		'get_supporter_or_register' => 'Get exclusive features and support the development of Lychee.<br />Unlock the <a href="https://lycheeorg.dev/get-supporter-edition/" class="text-primary-500 underline">Supporter Edition</a> or register your License key',
		'here' => 'here',
	],
	'dropbox' => [
		'not_configured' => 'Dropbox is not configured.',
	],
	'import_from_link' => [
		'instructions' => 'Please enter the direct link to a photo to import it:',
		'import' => 'Import',
	],
	'keybindings' => [
		'don_t_show_again' => 'Don\'t show this again',
		'side_wide' => 'Site-wide Shortcuts',
		'back_cancel' => 'Back/Cancel',
		'confirm' => 'Confirm',
		'login' => 'Login',
		'toggle_full_screen' => 'Toggle Full Screen',
		'toggle_sensitive_albums' => 'Toggle Sensitive Albums',

		'albums' => 'Albums Shortcuts',
		'new_album' => 'New Album',
		'upload_photos' => 'Upload Photos',
		'search' => 'Search',
		'show_this_modal' => 'Show this modal',
		'select_all' => 'Select All',
		'move_selection' => 'Move Selection',
		'delete_selection' => 'Delete Selection',

		'album' => 'Album Shortcuts',
		'slideshow' => 'Start/Stop Slideshow',
		'toggle' => 'Toggle panel',

		'photo' => 'Photo Shortcuts',
		'previous' => 'Previous photo',
		'next' => 'Next photo',
		'cycle' => 'Cycle overlay mode',
		'star' => 'Star the photo',
		'move' => 'Move the photo',
		'delete' => 'Delete the photo',
		'edit' => 'Edit information',
		'show_hide_meta' => 'Show information',

		'keep_hidden' => 'We will keep it hidden.',
	],
	'login' => [
		'username' => 'Username',
		'password' => 'Password',
		'unknown_invalid' => 'Unknown user or invalid password.',
		'signin' => 'Sign-In',
	],
	'register' => [
		'enter_license' => 'Enter your license key below:',
		'license_key' => 'License key',
		'invalid_license' => 'Invalid license key.',
		'register' => 'Register',
	],
	'share_album' => [
		'url_copied' => 'Copied URL to clipboard!',
	],
	'upload' => [
		'completed' => 'Completed',
		'uploaded' => 'Uploaded:',
		'release' => 'Release file to upload!',
		'select' => 'Click here to select files to upload',
		'drag' => '(Or drag files to the page)',
		'loading' => 'Loading',
		'resume' => 'Resume',
		'uploading' => 'Uploading',
		'finished' => 'Finished',
		'failed_error' => 'Upload failed. The server returned an error!',
	],
	'visibility' => [
		'public' => 'Public',
		'public_expl' => 'Anonymous users can access this album, subject to the restrictions below.',
		'full' => 'Original',
		'full_expl' => 'Anonymous users can view full-resolution photos.',
		'hidden' => 'Hidden',
		'hidden_expl' => 'Anonymous users need a direct link to access this album.',
		'downloadable' => 'Downloadable',
		'downloadable_expl' => 'Anonymous users can download this album.',
		'password' => 'Password',
		'password_prot' => 'Password protected',
		'password_prot_expl' => 'Anonymous users need a shared password to access this album.',
		'nsfw' => 'Sensitive',
		'nsfw_expl' => 'Album contains sensitive content.',
		'visibility_updated' => 'Visibility updated.',
	],
	'move_album' => [
		'confirm_single' => 'Are you sure you want to move the album “%1$s” into the album “%2$s”?',
		'confirm_multiple' => 'Are you sure you want to move all selected albums into the album “%s”?',
		'move_single' => 'Move Album',
		'move_to' => 'Move to',
		'move_to_single' => 'Move %s to:',
		'move_to_multiple' => 'Move %d albums to:',
		'no_album_target' => 'No album to move to',
		'moved_single' => 'Album moved!',
		'moved_single_details' => '%1$s moved to %2$s',
		'moved_details' => 'Album(s) moved to %s',
	],
	'new_album' => [
		'menu' => 'Create Album',
		'info' => 'Enter a title for the new album:',
		'title' => 'title',
		'create' => 'Create Album',
	],
	'new_tag_album' => [
		'menu' => 'Create Tag Album',
		'info' => 'Enter a title for the new tag album:',
		'title' => 'title',
		'set_tags' => 'Set tags to show',
		'warn' => 'Make sure to press enter after each tag',
		'create' => 'Create Tag Album',
	],
	'delete_album' => [
		'confirmation' => 'Are you sure you want to delete the album “%s” and all of the photos it contains?',
		'confirmation_multiple' => 'Are you sure you want to delete all %d selected albums and all of the photos they contain?',
		'warning' => 'This action can not be undone!',
		'delete' => 'Delete Album and Photos',
	],
	'transfer' => [
		'query' => 'Transfer ownership of album to',
		'confirmation' => 'Are you sure you want to transfer the ownership of album “%s” and all the photos it contains to "%s"?',
		'lost_access_warning' => 'Your access to this album will be lost.',
		'warning' => 'This action can not be undone!',
		'transfer' => 'Transfer ownership of album and photos',
	],
	'rename' => [
		'photo' => 'Enter a new title for this photo:',
		'album' => 'Enter a new title for this album:',
		'rename' => 'Rename',
	],
	'merge' => [
		'merge_to' => 'Merge %s to:',
		'merge_to_multiple' => 'Merge %d albums to:',
		'no_albums' => 'No albums to merge to.',
		'confirm' => 'Are you sure you want to merge the album “%1$s” into the album “%2$s”?',
		'confirm_multiple' => 'Are you sure you want to merge all selected albums into the album “%s”?',
		'merge' => 'Merge Albums',
		'merged' => 'Album(s) merged to %s!',
	],
	'unlock' => [
		'password_required' => 'This album is protected by a password. Enter the password below to view the photos of this album:',
		'password' => 'Password',
		'unlock' => 'Unlock',
	],
	'photo_tags' => [
		'question' => 'Enter your tags for this photo.',
		'question_multiple' => 'Enter your tags for all %d selected photos. Existing tags will be overwritten.',
		'no_tags' => 'No Tags',
		'set_tags' => 'Set Tags',
		'updated' => 'Tags updated!',
		'tags_override_info' => 'If this is unchecked, the tags will be added to the existing tags of the photo.',
	],
	'photo_copy' => [
		'no_albums' => 'No albums to copy to',
		'copy_to' => 'Copy %s to:',
		'copy_to_multiple' => 'Copy %d photos to:',
		'confirm' => 'Copy %s to %s.',
		'confirm_multiple' => 'Copy %d photos to %s.',
		'copy' => 'Copy',
		'copied' => 'Photo(s) copied!',
	],
	'photo_delete' => [
		'confirm' => 'Are you sure you want to delete the photo “%s”?',
		'confirm_multiple' => 'Are you sure you want to delete all %d selected photos?',
		'deleted' => 'Photo(s) deleted!',
	],
	'move_photo' => [
		'move_single' => 'Move %s to:',
		'move_multiple' => 'Move %d photos to:',
		'confirm' => 'Move %s to %s.',
		'confirm_multiple' => 'Move %d photos to %s.',
		'moved' => 'Photo(s) moved to %s!',
	],
	'target_user' => [
		'placeholder' => 'Select user',
	],
	'target_album' => [
		'placeholder' => 'Select album',
	],
	'webauthn' => [
		'u2f' => 'U2F',
		'success' => 'Authentication successful!',
		'error' => 'Whoops, it looks like something went wrong. Please reload the site and try again!',
	],
	'se' => [
		'available' => 'Available in the Supporter Edition',
	],
	'session_expired' => [
		'title' => 'Session expired',
		'message' => 'Your session has expired.<br />Please reload the page.',
		'reload' => 'Reload',
		'go_to_gallery' => 'Go to the Gallery',
	],
];