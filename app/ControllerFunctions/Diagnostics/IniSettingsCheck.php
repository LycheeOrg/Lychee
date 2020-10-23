<?php

namespace App\ControllerFunctions\Diagnostics;

use App\Models\Configs;

class IniSettingsCheck implements DiagnosticCheckInterface
{
	/**
	 * Return true if the upload_max_filesize is bellow what we want.
	 */
	private function convert_size(string $size): int
	{
		$size = trim($size);
		$last = strtolower($size[strlen($size) - 1]);
		$size = intval($size);

		switch ($last) {
			case 'g':
				$size *= 1024;
				// no break
			case 'm':
				$size *= 1024;
				// no break
			case 'k':
				$size *= 1024;
		}

		return $size;
	}

	public function check(array &$errors): void
	{
		// Check php.ini Settings
		// Load settings
		$settings = Configs::get();

		if (
			$this->convert_size(ini_get('upload_max_filesize')) < $this->convert_size('30M')
		) {
			$errors[]
				= 'Warning: You may experience problems when uploading a photo of large size. Take a look in the FAQ for details.';
		}
		if (
			$this->convert_size(ini_get('post_max_size')) < $this->convert_size('100M')
		) {
			$errors[]
				= 'Warning: You may experience problems when uploading a photos of large size. Take a look in the FAQ for details.';
		}
		if (
			intval(ini_get('max_execution_time')) < 200
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
