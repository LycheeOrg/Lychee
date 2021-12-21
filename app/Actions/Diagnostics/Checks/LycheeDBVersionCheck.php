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

	/**
	 * TBD.
	 *
	 * The following line of codes are duplicated in
	 *  - {@link \App\Actions\Update\Check::getCode()}
	 *  - {@link \App\Http\Middleware\Checks\IsMigrated::assert()}.
	 *
	 * TODO: Probably, the whole logic around installation and updating should be re-factored. The whole code is wicked.
	 *
	 * @param string[] $errors list of error messages
	 *
	 * @return void
	 */
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
