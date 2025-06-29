<?php
return [
    /*
	|--------------------------------------------------------------------------
	| Users page
	|--------------------------------------------------------------------------
	*/
    'title' => 'Users',
    'description' => 'Her kan du administrere brukerne av Lychee-installasjonen din. Du kan opprette, redigere og slette brukere.',
    'create' => 'Create a new user',
    'username' => 'Username',
    'password' => 'Password',
    'legend' => 'Legend',
    'upload_rights' => 'When selected, the user can upload content.',
    'edit_rights' => 'When selected, the user can modify their profile (username, password).',
    'quota' => 'When set, the user has a space quota for pictures (in kB).',
    'user_deleted' => 'User deleted',
    'user_created' => 'User created',
    'user_updated' => 'User updated',
    'change_saved' => 'Change saved!',
    'create_edit' => [
        'upload_rights' => 'User can upload content.',
        'edit_rights' => 'User can modify their profile (username, password).',
        'admin_rights' => 'User has admin rights.',
        'quota' => 'User has quota limit.',
        'quota_kb' => 'quota in kB (0 for default)',
        'note' => 'Admin note (not publically visible)',
        'create' => 'Create',
        'edit' => 'Edit',
    ],
    'invite' => [
        'button' => 'Invite user',
        'links_are_not_revokable' => 'Invitation links are not revokable.',
        'link_is_valid_x_days' => 'This link is valid for %d days.',
    ],
    'line' => [
		'owner' => 'Owner',
        'admin' => 'Admin user',
        'edit' => 'Edit',
        'delete' => 'Delete',
    ],
];
