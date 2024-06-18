<?php

declare(strict_types=1);

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