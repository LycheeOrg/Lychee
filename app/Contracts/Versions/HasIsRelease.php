<?php

namespace App\Contracts\Versions;

/**
 * Must implement release getter.
 */
interface HasIsRelease
{
	/**
	 * Return true if current instance is a release.
	 *
	 * @return bool
	 */
	public function isRelease(): bool;
}