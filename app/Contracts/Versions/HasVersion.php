<?php

declare(strict_types=1);

namespace App\Contracts\Versions;

use App\DTO\Version;

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