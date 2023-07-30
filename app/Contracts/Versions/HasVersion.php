<?php

namespace App\Contracts\Versions;

use App\Data\Version;

/**
 * Must implement version getter.
 */
interface HasVersion
{
	/**
	 * Return version stored.
	 *
	 * @return Version
	 */
	public function getVersion(): Version;
}