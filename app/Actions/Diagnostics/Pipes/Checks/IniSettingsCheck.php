<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\Facades\Helpers;
use App\Models\Configs;
use LycheeVerify\Verify;
use function Safe\ini_get;
use function Safe\preg_match;

/**
 * Double check that the init settings are not too low.
 * This informs us if something may not be uploaded because too big.
 */
class IniSettingsCheck implements DiagnosticPipe
{
	public function __construct(private Verify $verify)
	{
	}

	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		// Check php.ini Settings
		// Load settings
		$settings = Configs::get();

		if (!$this->verify->validate()) {
			$data[] = DiagnosticData::warn('Your installation has been tampered. Please verify the integrity of your files.', self::class);
		}

		if (Helpers::convertSize(ini_get('upload_max_filesize')) < Helpers::convertSize(('30M'))) {
			$data[] = DiagnosticData::warn('You may experience problems when uploading a photo of large size. Take a look in the FAQ for details.', self::class);
		}
		if (Helpers::convertSize(ini_get('post_max_size')) < Helpers::convertSize(('100M'))) {
			$data[] = DiagnosticData::warn('You may experience problems when uploading a photo of large size. Take a look in the FAQ for details.', self::class);
		}
		$max_execution_time = intval(ini_get('max_execution_time'));
		if (0 < $max_execution_time && $max_execution_time < 200) {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::warn('You may experience problems when uploading a photo of large size or handling many/large albums. Take a look in the FAQ for details.', self::class);
			// @codeCoverageIgnoreEnd
		}
		if (filter_var(ini_get('allow_url_fopen'), FILTER_VALIDATE_BOOLEAN) !== true) {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::warn('You may experience problems with the Dropbox- and URL-Import. Edit your php.ini and set allow_url_fopen to 1.', self::class);
			// @codeCoverageIgnoreEnd
		}

		// Check imagick
		if (!extension_loaded('imagick')) {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::warn('Pictures that are rotated lose their metadata! Please install Imagick to avoid that.', self::class);
		// @codeCoverageIgnoreEnd
		} else {
			if (!isset($settings['imagick'])) {
				// @codeCoverageIgnoreStart
				$data[] = DiagnosticData::warn('Pictures that are rotated lose their metadata! Please enable Imagick in settings to avoid that.', self::class);
				// @codeCoverageIgnoreEnd
			}
		}

		if (!Helpers::isExecAvailable()) {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::warn('exec function has been disabled. You may experience some error 500, please report them to us.', self::class);
			// @codeCoverageIgnoreEnd
		}

		if (preg_match('!^[-_a-zA-Z]+/\d+(\.\d+)*[a-z]? \(.*\)!', ini_get('user_agent')) === 0) {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::warn('user_agent for PHP is not properly set. You may experience problems when importing images via URL.', self::class);
			// @codeCoverageIgnoreEnd
		}

		if (extension_loaded('xdebug')) {
			// @codeCoverageIgnoreStart
			$msg = config('app.debug') !== true
			? DiagnosticData::error('xdebug is enabled although Lychee is not in debug mode. Outside of debugging, xdebug will generate significant slowdown on your application.', self::class)
			: DiagnosticData::warn('xdebug is enabled. This will generate significant slowdown on your application.', self::class);
			$data[] = $msg;
			// @codeCoverageIgnoreEnd
		}

		if (extension_loaded('xdebug')) {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::info('xdebug mode:' . ini_get('xdebug.mode'), self::class);
			$data[] = DiagnosticData::info('xdebug start_with_request:' . ini_get('xdebug.start_with_request'), self::class);
			// @codeCoverageIgnoreEnd
		}

		if (ini_get('assert.exception') !== '1') {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::warn('assert.exception is set to false. Lychee assumes that failing assertions throw proper exceptions.', self::class);
			// @codeCoverageIgnoreEnd
		}

		if (ini_get('zend.assertions') !== '-1' && config('app.debug') !== true) {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::warn('zend.assertions is enabled although Lychee is not in debug mode. Outside of debugging, code generation for assertions is recommended to be disabled for efficiency reasons', self::class);
			// @codeCoverageIgnoreEnd
		}

		if (ini_get('zend.assertions') !== '1' && config('app.debug') === true) {
			$data[] = DiagnosticData::warn('zend.assertions is disabled although Lychee is in debug mode. For easier debugging code generation for assertions should be enabled.', self::class);
		}

		$disabledFunctions = explode(',', ini_get('disable_functions'));
		$tmpfileExists = function_exists('tmpfile') && !in_array('tmpfile', $disabledFunctions, true);
		if ($tmpfileExists !== true) {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::error('tmpfile() is disabled, this will prevent you from uploading pictures.', self::class);
			// @codeCoverageIgnoreEnd
		}

		$path = sys_get_temp_dir();
		if (!is_writable($path)) {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::error('sys_get_temp_dir() is not writable, this will prevent you from uploading pictures.', self::class);
			// @codeCoverageIgnoreEnd
		}
		if (!is_readable($path)) {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::error('sys_get_temp_dir() is not readable, this will prevent you from uploading pictures.', self::class);
			// @codeCoverageIgnoreEnd
		}

		return $next($data);
	}
}
