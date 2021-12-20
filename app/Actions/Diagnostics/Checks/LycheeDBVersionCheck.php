<?php

namespace App\Actions\Diagnostics\Checks;

use App\Contracts\DiagnosticCheckInterface;
use App\Metadata\LycheeVersion;

class LycheeDBVersionCheck implements DiagnosticCheckInterface
{
	private LycheeVersion $lycheeVersion;

	/**
	 * @param LycheeVersion $lycheeVersion
	 * @param array caching the return of lycheeVersion->get()
	 */
	public function __construct(
		LycheeVersion $lycheeVersion
	) {
		$this->lycheeVersion = $lycheeVersion;
	}

	public function check(array &$errors): void
	{
		if ($this->lycheeVersion->isRelease) {
			$db_ver = $this->lycheeVersion->getDBVersion();
			$file_ver = $this->lycheeVersion->getFileVersion();
			if ($db_ver->toInteger() < $file_ver->toInteger()) {
				$errors[] = 'Error: Database is behind file versions. Please apply the migration.';
			}
		}
	}
}
