<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

return [
	/*
	|--------------------------------------------------------------------------
	| Jobs page
	|--------------------------------------------------------------------------
	*/
	'title' => 'Gallery',

	'smart_albums' => 'Smart albums',
	'albums' => 'Albums',
	'root' => 'Albums',

	'original' => 'Original',
	'medium' => 'Medium',
	'medium_hidpi' => 'Medium HiDPI',
	'small' => 'Thumb',
	'small_hidpi' => 'Thumb HiDPI',
	'thumb' => 'Square thumb',
	'thumb_hidpi' => 'Square thumb HiDPI',
	'placeholder' => 'Low Quality Image Placeholder',
	'thumbnail' => 'Photo thumbnail',
	'live_video' => 'Video part of live-photo',

	'camera_data' => 'Camera date',
	'album_reserved' => 'All Rights Reserved',

	'map' => [
		'error_gpx' => 'Error loading GPX file',
		'osm_contributors' => 'OpenStreetMap contributors',
	],

	'search' => [
		'title' => 'Search',
		'searching' => 'Searching…',
		'no_results' => 'Nothing matches your search query.',
		'searchbox' => 'Search…',
		'minimum_chars' => 'Minimum %s characters required.',
		'photos' => 'Photos (%s)',
		'albums' => 'Albums (%s)',
	],

	'smart_album' => [
		'unsorted' => 'Unsorted',
		'starred' => 'Starred',
		'recent' => 'Recent',
		'public' => 'Public',
		'on_this_day' => 'On This Day',
	],

	'layout' => [
		'squares' => 'Square thumbnails',
		'justified' => 'With aspect, justified',
		'masonry' => 'With aspect, masonry',
		'grid' => 'With aspect, grid',
	],

	'overlay' => [
		'none' => 'None',
		'exif' => 'EXIF data',
		'description' => 'Description',
		'date' => 'Date taken',
	],

	'timeline' => [
		'default' => 'default',
		'disabled' => 'disabled',
		'year' => 'Year',
		'month' => 'Month',
		'day' => 'Day',
		'hour' => 'Hour',
	],

	'album' => [
		'header_albums' => 'Albums',
		'header_photos' => 'Photos',
		'no_results' => 'Nothing to see here',
		'upload' => 'Upload photos',

		'tabs' => [
			'about' => 'About Album',
			'share' => 'Share Album',
			'move' => 'Move Album',
			'danger' => 'DANGER ZONE',
		],

		'hero' => [
			'created' => 'Created',
			'copyright' => 'Copyright',
			'subalbums' => 'Subalbums',
			'images' => 'Photos',
			'download' => 'Download Album',
			'share' => 'Share Album',
			'stats_only_se' => 'Statistics available in the Supporter Edition',
		],

		'stats' => [
			'lens' => 'Lens',
			'shutter' => 'Shutter speed',
			'iso' => 'ISO',
			'model' => 'Model',
			'aperture' => 'Aperture',
			'no_data' => 'No data',
		],

		'properties' => [
			'title' => 'Title',
			'description' => 'Description',
			'photo_ordering' => 'Order photos by',
			'children_ordering' => 'Order albums by',
			'asc/desc' => 'asc/desc',
			'header' => 'Set album header',
			'compact_header' => 'Use compact header',
			'license' => 'Set license',
			'copyright' => 'Set copyright',
			'aspect_ratio' => 'Set album thumbs aspect ratio',
			'album_timeline' => 'Set album timeline mode',
			'photo_timeline' => 'Set photo timeline mode',
			'layout' => 'Set photo layout',
			'show_tags' => 'Set tags to show',
			'tags_required' => 'Tags are required.',
		],
	],

	'photo' => [
		'actions' => [
			'star' => 'Star',
			'unstar' => 'Unstar',
			'set_album_header' => 'Set as album header',
			'move' => 'Move',
			'delete' => 'Delete',
			'header_set' => 'Header set',
		],

		'details' => [
			'about' => 'About',
			'basics' => 'Basics',
			'title' => 'Title',
			'uploaded' => 'Uploaded',
			'description' => 'Description',
			'license' => 'License',
			'reuse' => 'Reuse',
			'latitude' => 'Latitude',
			'longitude' => 'Longitude',
			'altitude' => 'Altitude',
			'location' => 'Location',
			'image' => 'Image',
			'video' => 'Video',
			'size' => 'Size',
			'format' => 'Format',
			'resolution' => 'Resolution',
			'duration' => 'Duration',
			'fps' => 'Frame rate',
			'tags' => 'Tags',
			'camera' => 'Camera',
			'captured' => 'Captured',
			'make' => 'Make',
			'type' => 'Type/Model',
			'lens' => 'Lens',
			'shutter' => 'Shutter Speed',
			'aperture' => 'Aperture',
			'focal' => 'Focal Length',
			'iso' => 'ISO %s',
		],

		'edit' => [
			'set_title' => 'Set Title',
			'set_description' => 'Set Description',
			'set_license' => 'Set License',
			'no_tags' => 'No Tags',
			'set_tags' => 'Set Tags',
			'set_created_at' => 'Set Upload Date',
			'set_taken_at' => 'Set Taken Date',
			'set_taken_at_info' => 'When set, a star %s will be displayed to indicate that this date is not the original EXIF date.<br>Untick the checkbox and save to reset to the original date.',
		],
	],

	'nsfw' => [
		'header' => 'Sensitive content',
		'description' => 'This album contains sensitive content which some people may find offensive or disturbing.',
		'consent' => 'Tap to consent.',
	],

	'menus' => [
		'star' => 'Star',
		'unstar' => 'Unstar',
		'star_all' => 'Star Selected',
		'unstar_all' => 'Unstar Selected',
		'tag' => 'Tag',
		'tag_all' => 'Tag Selected',
		'set_cover' => 'Set Album Cover',
		'remove_header' => 'Remove Album Header',
		'set_header' => 'Set Album Header',
		'copy_to' => 'Copy to …',
		'copy_all_to' => 'Copy Selected to …',
		'rename' => 'Rename',
		'move' => 'Move',
		'move_all' => 'Move Selected',
		'delete' => 'Delete',
		'delete_all' => 'Delete Selected',
		'download' => 'Download',
		'download_all' => 'Download Selected',
		'merge' => 'Merge',
		'merge_all' => 'Merge Selected',

		'upload_photo' => 'Upload Photo',
		'import_link' => 'Import from Link',
		'import_dropbox' => 'Import from Dropbox',
		'new_album' => 'New Album',
		'new_tag_album' => 'New Tag Album',
		'upload_track' => 'Upload track',
		'delete_track' => 'Delete track',
	],

	'sort' => [
		'photo_select_1' => 'Upload Time',
		'photo_select_2' => 'Take Date',
		'photo_select_3' => 'Title',
		'photo_select_4' => 'Description',
		'photo_select_6' => 'Star',
		'photo_select_7' => 'Photo Format',
		'ascending' => 'Ascending',
		'descending' => 'Descending',
		'album_select_1' => 'Creation Time',
		'album_select_2' => 'Title',
		'album_select_3' => 'Description',
		'album_select_5' => 'Latest Take Date',
		'album_select_6' => 'Oldest Take Date',
	],

	'albums_protection' => [
		'private' => 'private',
		'public' => 'public',
		'inherit_from_parent' => 'inherit from parent',
	],
];