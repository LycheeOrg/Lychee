<?php

namespace App\Models\Extensions;

use App\Assets\Helpers;
use App\Models\Configs;
use Exception;

trait PhotoGetters
{
	/**
	 * Retun the shutter speed as a proper string.
	 */
	public function get_shutter_str()
	{
		$shutter = $this->shutter;
		// shutter speed needs to be processed. It is stored as a string `a/b s`
		if ($shutter != '' && substr($shutter, 0, 2) != '1/') {
			preg_match('/(\d+)\/(\d+) s/', $shutter, $matches);
			if ($matches) {
				$a = intval($matches[1]);
				$b = intval($matches[2]);
				if ($b != 0) {
					try {
						$gcd = Helpers::gcd($a, $b);
						$a = $a / $gcd;
						$b = $b / $gcd;
					} catch (Exception $e) {
						// this should not happen as we covered the case $b = 0;
					}
					if ($a == 1) {
						$shutter = '1/' . $b . ' s';
					} else {
						$shutter = ($a / $b) . ' s';
					}
				}
			}
		}

		if ($shutter == '1/1 s') {
			$shutter = '1 s';
		}

		return $shutter;
	}

	/**
	 * Get the public value of a picture
	 * if 0 : picture is private
	 * if 1 : picture is public alone
	 * if 2 : picture is public by album being public (if being in an album).
	 *
	 * @return string
	 */
	public function get_public()
	{
		$ret = $this->public == 1 ? '1' : '0';

		if ($this->album_id != null) {
			$ret = $this->album->public == '1' ? '2' : $ret;
		}

		return $ret;
	}

	/**
	 * Return the Album license or the default one.
	 *
	 * @param string $license = album License
	 *
	 * @return string
	 */
	public function get_license(string $license = 'none'): string
	{
		if ($this->license != 'none') {
			return $this->license;
		}

		if ($license != 'none') {
			return $license;
		}

		return Configs::get_value('default_license');
	}
}
