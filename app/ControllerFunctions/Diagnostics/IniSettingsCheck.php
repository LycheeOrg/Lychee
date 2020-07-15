<?php

namespace App\ControllerFunctions\Diagnostics;

use App\Configs;

class IniSettingsCheck implements DiagnosticCheckInterface
{
	public function check(array &$errors): void
	{
		// Check php.ini Settings
		// Load settings
		$settings = Configs::get();

		if (
			ini_get('max_execution_time') < 200
			&& ini_set('upload_max_filesize', '20M') === false
		) {
			$errors[]
				= 'Warning: You may experience problems when uploading a large amount of photos. Take a look in the FAQ for details.';
		}
		if (empty(ini_get('allow_url_fopen'))) {
			$errors[]
				= 'Warning: You may experience problems with the Dropbox- and URL-Import. Edit your php.ini and set allow_url_fopen to 1.';
		}

		// Check imagick
		if (!extension_loaded('imagick')) {
			$errors[]
				= 'Warning: Pictures that are rotated lose their metadata! Please install Imagick to avoid that.';
		} else {
			if (!isset($settings['imagick'])) {
				$errors[]
					= 'Warning: Pictures that are rotated lose their metadata! Please enable Imagick in settings to avoid that.';
			}
		}

		if (!function_exists('exec')) {
			$errors[]
				= 'Warning: exec function has been disabled. You may experience some error 500, please report them to us.';
		}
	}
}
