<?php

namespace App\Http\Middleware\Checks;

use App\Contracts\MiddlewareCheck;
use App\Metadata\LycheeVersion;

class IsMigrated implements MiddlewareCheck
{
	/**
	 * @var LycheeVersion
	 */
	private $lycheeVersion;

	public function __construct(LycheeVersion $lycheeVersion)
	{
		$this->lycheeVersion = $lycheeVersion;
	}

	public function assert(): bool
	{
		return false;

		return $this->lycheeVersion->getDBVersion()['version'] < $this->lycheeVersion->getFileVersion()['version'];
	}
}
