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
	$oups = new PanicAttack();
	$oups->apacheRewrite();
}

/*
 |--------------------------------------------------------------------------
 | Catch error (worse case scenario)
 |--------------------------------------------------------------------------
 |
 | Try-catch does not work on require. As a result we use the
 | register_shutdown_function to handle such errors.
 |
 */
function panicHelp()
{
	$last_error = error_get_last();
	if ($last_error && ($last_error['type'] == E_ERROR || $last_error['type'] == E_COMPILE_ERROR)) {
		$oups = new PanicAttack();
		$message = substr($last_error['message'], 0, 200);
		$oups->handle($message);
	}
}
register_shutdown_function('panicHelp');
