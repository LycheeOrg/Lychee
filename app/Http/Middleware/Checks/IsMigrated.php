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

	/**
	 * @param string $version in the shape of xxyyzz
	 *
	 * @return string xx.yy.zz
	 */
	private function intify(string $version): int
	{
		$v = explode('.', $version);

		return 10000 * ($v[0] ?? 0) + 100 * ($v[1] ?? 0) + ($v[2] ?? 0);
	}

	public function assert(): bool
	{
		$db_ver = $this->lycheeVersion->getDBVersion()['version'];
		$file_ver = $this->lycheeVersion->getFileVersion()['version'];

		return $this->intify($db_ver) == $this->intify($file_ver);
	}
}
