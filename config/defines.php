<?php

return [
    'path' => [
        'LYCHEE' => substr(__DIR__, 0, -6),
    ],

	// Define status
    'status' => [
	    'LYCHEE_STATUS_NOCONFIG' => 0,
	    'LYCHEE_STATUS_LOGGEDOUT' => 1,
	    'LYCHEE_STATUS_LOGGEDIN' => 2,
    ],

	// Define dirs
    'dirs' => [
	    'LYCHEE_DIST' => public_path('dist'),
	    'LYCHEE_UPLOADS' => public_path('uploads'),
	    'LYCHEE_UPLOADS_BIG' => public_path('uploads/big/'),
	    'LYCHEE_UPLOADS_MEDIUM' => public_path('uploads/medium/'),
	    'LYCHEE_UPLOADS_SMALL' => public_path('uploads/small/'),
	    'LYCHEE_UPLOADS_THUMB' => public_path('uploads/thumb/'),
	    'LYCHEE_UPLOADS_IMPORT' => public_path('uploads/import/'),
    ],

	// Define urls
    'urls' => [
	    'LYCHEE_URL_UPLOADS_BIG' => 'uploads/big/',
	    'LYCHEE_URL_UPLOADS_MEDIUM' => 'uploads/medium/',
	    'LYCHEE_URL_UPLOADS_SMALL' => 'uploads/small/',
	    'LYCHEE_URL_UPLOADS_THUMB' => 'uploads/thumb/',
    ],

    'defaults' => [
    	'SITE_TITLE' => 'Lychee v4',
    ],
];
