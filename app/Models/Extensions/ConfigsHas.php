<?php

namespace App\Models\Extensions;

use App\Exceptions\ExternalComponentMissingException;
use App\Exceptions\Handler;
use App\Exceptions\Internal\InvalidConfigOption;
use App\Exceptions\Internal\QueryBuilderException;
use App\Facades\Helpers;

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
				} catch (\Exception $e) {
					$cmd_output = false;
					Handler::reportSafely(new ExternalComponentMissingException('could not find exiftool; `has_exiftool` will be set to 0', $e));
				}
				$path = $cmd_output === false ? '' : $cmd_output;
				$has_exiftool = $path === '' ? 0 : 1;
			} else {
				$has_exiftool = 0;
			}

			try {
				self::set('has_exiftool', $has_exiftool);
			} catch (InvalidConfigOption|QueryBuilderException $e) {
				// If we could not save the detected setting, still proceed
				Handler::reportSafely($e);
			}
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
				} catch (\Exception $e) {
					$cmd_output = false;
					Handler::reportSafely(new ExternalComponentMissingException('could not find ffmpeg; `has_ffmpeg` will be set to 0', $e));
				}
				$path = $cmd_output === false ? '' : $cmd_output;
				$has_ffmpeg = $path === '' ? 0 : 1;
			} else {
				$has_ffmpeg = 0;
			}

			try {
				self::set('has_ffmpeg', $has_ffmpeg);
			} catch (InvalidConfigOption|QueryBuilderException $e) {
				// If we could not save the detected setting, still proceed
				Handler::reportSafely($e);
			}
		}

		return $has_ffmpeg === 1;
	}
}
