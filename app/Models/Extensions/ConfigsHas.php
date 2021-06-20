<?php

namespace App\Models\Extensions;

use App\Models\Logs;
use Exception;

trait ConfigsHas
{
	/**
	 * @return bool returns the Imagick setting
	 */
	public static function hasImagick()
	{
		if ((bool) (extension_loaded('imagick') && self::get_value('imagick', '1') == '1')) {
			return true;
		}
		try {
			Logs::notice(__METHOD__, __LINE__, 'hasImagick : false');
		} catch (Exception $e) {
			//do nothing
		}

		return false;
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

		$has_exiftool = intval(self::get_value('has_exiftool'));

		// value not yet set -> let's see if exiftool is available
		if ($has_exiftool == 2) {
			try {
				$path = exec('command -v exiftool');
				if ($path == '') {
					self::set('has_exiftool', 0);
					$has_exiftool = false;
				} else {
					self::set('has_exiftool', 1);
					$has_exiftool = true;
				}
			} catch (Exception $e) {
				self::set('has_exiftool', 0);
				$has_exiftool = false;
				Logs::warning(__METHOD__, __LINE__, 'exec is disabled, has_exiftool has been set to 0.');
			}
		} elseif ($has_exiftool == 1) {
			$has_exiftool = true;
		} else {
			$has_exiftool = false;
		}

		return $has_exiftool;
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

		$has_ffmpeg = intval(self::get_value('has_ffmpeg'));

		// value not yet set -> let's see if ffmpeg is available
		if ($has_ffmpeg == 2) {
			try {
				$path = exec('command -v ffmpeg');
				if ($path == '') {
					self::set('has_ffmpeg', 0);
					$has_ffmpeg = false;
				} else {
					self::set('has_ffmpeg', 1);
					$has_ffmpeg = true;
				}
			} catch (Exception $e) {
				self::set('has_ffmpeg', 0);
				$has_ffmpeg = false;
				Logs::warning(__METHOD__, __LINE__, 'exec is disabled, set_ffmpeg has been set to 0.');
			}
		} elseif ($has_ffmpeg == 1) {
			$has_ffmpeg = true;
		} else {
			$has_ffmpeg = false;
		}

		return $has_ffmpeg;
	}
}
