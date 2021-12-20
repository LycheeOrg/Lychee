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
		$db_ver = $this->lycheeVersion->getDBVersion();
		$file_ver = $this->lycheeVersion->getFileVersion();

		return $db_ver->toInteger() === $file_ver->toInteger();
	}
}
