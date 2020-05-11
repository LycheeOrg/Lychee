<?php

/*
 |--------------------------------------------------------------------------
 | If we are running Apache, mod_rewrite needs to be available
 |--------------------------------------------------------------------------
 |
 | This is because it is likely that the first call to Lychee will redirect
 | from / to /install via the middleware. Because there are not such
 | physical file, apache will return an error 404 which is easy to to
 | understand. We provided the relevant debuging info in the wiki but once
 | again we need a fool proof solution for those who do not apply the RTFM
 | protocol.
 */
if (function_exists('apache_get_modules') && !in_array('mod_rewrite', apache_get_modules())) {
	displaySimpleError('mod_rewrite is not enabled', 503, 'You are using apache but <code>mod_rewrite</code> is not enabled.<br>
	Please do: <code>a2enmod rewrite</code>');
	exit;
}

/*
 |--------------------------------------------------------------------------
 | Catch error where composer is loading properly.
 |--------------------------------------------------------------------------
 |
 | Try-catch does not work on require. As a result we use the
 | register_shutdown_function to handle such errors.
 |
 | Here we assume this will only fail if the file is not present as the
 | most probable error source. We set up a kind reminder that composer
 | needs to be run in order to have Lychee working.
 |
 | As there is no way to unregister such function in php we use the global
 | variable $composer_not_found to disable this behavior at later steps of
 | the execution.
 */
function onComposerNotFoundDie()
{
	global $composer_not_found;
	if ($composer_not_found) {
		displaySimpleError('vendor/autoload.php not found', 503, '<code>../vendor/autoload.php</code> not found.<br>
		Please do: <code>composer install --no-dev --prefer-dist</code>');
	}
}
register_shutdown_function('onComposerNotFoundDie');

