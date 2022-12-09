<?php

namespace App\Http\Middleware\Checks;

use App\Contracts\MiddlewareCheck;
use App\Metadata\Versions\LycheeVersion;

class IsMigrated implements MiddlewareCheck
{
	private LycheeVersion $lycheeVersion;

	public function __construct(LycheeVersion $lycheeVersion)
	{
		$this->lycheeVersion = $lycheeVersion;
	}

	/**
	 * TBD.
	 *
	 * The following line of codes are duplicated in
	 *  - {@link \App\Actions\Diagnostics\Checks\LycheeDBVersionCheck::check()}
	 *  - {@link \App\Actions\Update\Check::getCode()}.
	 *
	 * TODO: Probably, the whole logic around installation and updating should be re-factored. The whole code is wicked.
	 *
	 * @return bool
	 */
	public function assert(): bool
	{
		$db_ver = $this->lycheeVersion->getDBVersion();
		$file_ver = $this->lycheeVersion->getFileVersion();

		return $db_ver->toInteger() === $file_ver->toInteger();
	}
}
