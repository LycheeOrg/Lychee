<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

return [
	/*
	|--------------------------------------------------------------------------
	| People / Facial Recognition
	|--------------------------------------------------------------------------
	*/
	'title' => 'People',
	'description' => 'Browse photos by the people in them.',
	'no_people' => 'No people found yet. Scan some photos to detect faces.',
	'photos_label' => 'photo(s)',
	'faces_label' => 'face(s)',
	'hidden_faces' => 'face(s) hidden for privacy',
	'unknown' => 'Unknown',
	'confidence' => 'Confidence',
	'laplacian_variance' => 'Laplacian Variance',
	'assign_face' => 'Assign face',
	'dismiss_face' => 'Dismiss face',
	'undismiss_face' => 'Undismiss face',
	'scan_faces' => 'Scan for faces',
	'scanning' => 'Scanning for faces…',
	'scan_success' => 'Face scan queued successfully.',
	'not_searchable' => 'Hidden',
	'searchable' => 'Visible',
	'claim_by_selfie' => 'Find me in photos',
	'claim_by_selfie_description' => 'Upload a selfie to find and link your person profile.',
	'claims' => [
		'success' => 'Successfully linked to your profile.',
		'no_face' => 'No face detected in the selfie.',
		'no_match' => 'No matching person found.',
		'already_claimed' => 'This person is already linked to another user.',
		'low_confidence' => 'Match confidence too low. Please try a clearer photo.',
	],
	'person' => [
		'edit' => 'Edit',
		'delete' => 'Delete',
		'merge' => 'Merge into…',
		'toggle_searchable' => 'Toggle visibility',
		'claim' => 'This is me',
		'unclaim' => 'Unlink from me',
		'photos_title' => 'Photos of %s',
	],
	'clusters_title' => 'Face Clusters',
	'run_clustering' => 'Run Clustering',
	'no_clusters' => 'No clusters found. Run clustering to group detected faces.',
	'faces' => 'faces',
	'enter_name' => 'Person name…',
	'assign' => 'Assign',
	'dismiss' => 'Dismiss',
	'assignment' => [
		'title' => 'Assign face to person',
		'select_person' => 'Select existing person…',
		'new_person' => 'Or create new person',
		'new_person_placeholder' => 'New person name…',
		'confirm' => 'Assign',
		'cancel' => 'Cancel',
		'success' => 'Face assigned successfully.',
		'dismiss' => 'Dismiss',
		'dismissed' => 'Face dismissed successfully.',
	],
	'people_in_photo' => 'People in this photo',
	'remove_from_person' => 'Remove from person',
	'remove_from_person_success' => 'Face removed successfully.',
	'batch_select' => 'Select faces',
	'batch_cancel' => 'Cancel selection',
	'batch_selected' => ':count selected',
	'batch_assign' => 'Assign selected',
	'batch_unassign' => 'Unassign selected',
	'assign_to_user' => 'Assign to user',
	'search_user' => 'Search user…',
	'cluster_detail_title' => 'Cluster (:count faces)',
	'assigned_faces_to' => 'Assigned :count face(s) to ":name"',
	'assigned_faces' => 'Assigned :count face(s)',
	'dismissed_faces' => 'Dismissed :count face(s)',
	'clustering_started' => 'Clustering started. Reload when complete.',
	'merge' => [
		'title' => 'Merge person',
		'into' => 'into…',
		'select_target' => 'Select target person…',
		'warning' => 'This will move all faces from the source person to the target person and delete the source. This cannot be undone.',
		'confirm' => 'Merge',
		'success' => 'Persons merged successfully.',
	],
];
