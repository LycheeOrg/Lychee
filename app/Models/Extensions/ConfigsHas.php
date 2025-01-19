<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models\Extensions;

use App\Exceptions\ExternalComponentMissingException;
use App\Exceptions\Handler;
use App\Exceptions\Internal\InvalidConfigOption;
use App\Exceptions\Internal\QueryBuilderException;
use App\Facades\Helpers;
use function Safe\exec;

trait ConfigsHas
{
	/**
	 * @return bool returns the Imagick setting
	 */
	public static function hasImagick(): bool
	{
		return
			extension_loaded('imagick') &&
			self::getValueAsBool('imagick');
	}

	/**
	 * @return bool returns the Exiftool setting
	 */
	public static function hasExiftool(): bool
	{
		// has_exiftool has the following values:
		// 0: No Exiftool
		// 1: Exiftool is available
		// 2: Not yet tested if exiftool is available

		$has_exiftool = self::getValueAsInt('has_exiftool');

		// value not yet set -> let's see if exiftool is available
		if ($has_exiftool === 2) {
			if (Helpers::isExecAvailable()) {
				try {
					$cmd_output = exec('command -v exiftool');
					// @codeCoverageIgnoreStart
				} catch (\Exception $e) {
					$cmd_output = false;
					Handler::reportSafely(new ExternalComponentMissingException('could not find exiftool; `has_exiftool` will be set to 0', $e));
				}
				// @codeCoverageIgnoreEnd
				$path = $cmd_output === false ? '' : $cmd_output;
				$has_exiftool = $path === '' ? 0 : 1;
			} else {
				// @codeCoverageIgnoreStart
				$has_exiftool = 0;
				// @codeCoverageIgnoreEnd
			}

			try {
				self::set('has_exiftool', $has_exiftool);
				// @codeCoverageIgnoreStart
			} catch (InvalidConfigOption|QueryBuilderException $e) {
				// If we could not save the detected setting, still proceed
				Handler::reportSafely($e);
			}
			// @codeCoverageIgnoreEnd
		}

		return $has_exiftool === 1;
	}

	/**
	 * @return bool returns the FFMpeg setting
	 */
	public static function hasFFmpeg(): bool
	{
		// has_ffmpeg has the following values:
		// 0: No ffmpeg
		// 1: ffmpeg is available
		// 2: Not yet tested if ffmpeg is available

		$has_ffmpeg = self::getValueAsInt('has_ffmpeg');

		// value not yet set -> let's see if ffmpeg is available
		if ($has_ffmpeg === 2) {
			if (Helpers::isExecAvailable()) {
				try {
					$cmd_output = exec('command -v ffmpeg');
					// @codeCoverageIgnoreStart
				} catch (\Exception $e) {
					$cmd_output = false;
					Handler::reportSafely(new ExternalComponentMissingException('could not find ffmpeg; `has_ffmpeg` will be set to 0', $e));
				}
				// @codeCoverageIgnoreEnd
				$path = $cmd_output === false ? '' : $cmd_output;
				$has_ffmpeg = $path === '' ? 0 : 1;
			} else {
				// @codeCoverageIgnoreStart
				$has_ffmpeg = 0;
				// @codeCoverageIgnoreEnd
			}

			try {
				self::set('has_ffmpeg', $has_ffmpeg);
				// @codeCoverageIgnoreStart
			} catch (InvalidConfigOption|QueryBuilderException $e) {
				// If we could not save the detected setting, still proceed
				Handler::reportSafely($e);
			}
			// @codeCoverageIgnoreEnd
		}

		return $has_ffmpeg === 1;
	}
}
