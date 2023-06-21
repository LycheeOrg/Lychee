<?php

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\Facades\Helpers;
use App\Models\Configs;
use function Safe\ini_get;
use function Safe\preg_match;

class IniSettingsCheck implements DiagnosticPipe
{
	public function handle(array &$data, \Closure $next): array
	{
		// Check php.ini Settings
		// Load settings
		$settings = Configs::get();

		if ($this->convert_size(ini_get('upload_max_filesize')) < $this->convert_size('30M')) {
			$data[] = 'Warning: You may experience problems when uploading a photo of large size. Take a look in the FAQ for details.';
		}
		if ($this->convert_size(ini_get('post_max_size')) < $this->convert_size('100M')) {
			$data[] = 'Warning: You may experience problems when uploading a photo of large size. Take a look in the FAQ for details.';
		}
		$max_execution_time = intval(ini_get('max_execution_time'));
		if (0 < $max_execution_time && $max_execution_time < 200) {
			// @codeCoverageIgnoreStart
			$data[] = 'Warning: You may experience problems when uploading a photo of large size or handling many/large albums. Take a look in the FAQ for details.';
			// @codeCoverageIgnoreEnd
		}
		if (filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN) !== true) {
			// @codeCoverageIgnoreStart
			$data[] = 'Warning: You may experience problems with the Dropbox- and URL-Import. Edit your php.ini and set allow_url_fopen to 1.';
			// @codeCoverageIgnoreEnd
		}

		// Check imagick
		if (!extension_loaded('imagick')) {
			// @codeCoverageIgnoreStart
			$data[] = 'Warning: Pictures that are rotated lose their metadata! Please install Imagick to avoid that.';
			// @codeCoverageIgnoreEnd
		} else {
			if (!isset($settings['imagick'])) {
				// @codeCoverageIgnoreStart
				$data[] = 'Warning: Pictures that are rotated lose their metadata! Please enable Imagick in settings to avoid that.';
				// @codeCoverageIgnoreEnd
			}
		}

		if (!Helpers::isExecAvailable()) {
			// @codeCoverageIgnoreStart
			$data[] = 'Warning: exec function has been disabled. You may experience some error 500, please report them to us.';
			// @codeCoverageIgnoreEnd
		}

		if (preg_match('!^[-_a-zA-Z]+/\d+(\.\d+)*[a-z]? \(.*\)!', ini_get('user_agent')) === 0) {
			// @codeCoverageIgnoreStart
			$data[] = 'Warning: user_agent for PHP is not properly set. You may experience problems when importing images via URL.';
			// @codeCoverageIgnoreEnd
		}

		if (ini_get('assert.exception') !== '1') {
			// @codeCoverageIgnoreStart
			$data[] = 'Warning: assert.exception is set to false. Lychee assumes that failing assertions throw proper exceptions.';
			// @codeCoverageIgnoreEnd
		}

		if (ini_get('zend.assertions') !== '-1' && config('app.debug') !== true) {
			// @codeCoverageIgnoreStart
			$data[] = 'Warning: zend.assertions is enabled although Lychee is not in debug mode. Outside of debugging, code generation for assertions is recommended to be disabled for efficiency reasons';
			// @codeCoverageIgnoreEnd
		}

		if (ini_get('zend.assertions') !== '1' && config('app.debug') === true) {
			$data[] = 'Warning: zend.assertions is disabled although Lychee is in debug mode. For easier debugging code generation for assertions should be enabled.';
		}

		return $next($data);
	}

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
}
