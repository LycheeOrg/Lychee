<?php

namespace App\Contracts\Versions;

/**
 * Must implement release getter.
 */
interface HasRelease
{
	/**
	 * Return true if current instance is a release.
	 *
	 * @return bool
	 */
	public function isRelease(): bool;
}