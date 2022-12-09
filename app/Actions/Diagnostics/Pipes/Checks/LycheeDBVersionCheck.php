<?php

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\Metadata\Versions\LycheeVersion;

class LycheeDBVersionCheck implements DiagnosticPipe
{
	private LycheeVersion $lycheeVersion;

	/**
	 * @param LycheeVersion $lycheeVersion
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
	 *  - {@link \App\Actions\InstallUpdate\Check::getCode()}
	 *  - {@link \App\Http\Middleware\Checks\IsMigrated::assert()}.
	 *
	 * TODO: Probably, the whole logic around installation and updating should be re-factored. The whole code is wicked.
	 *
	 * @param array<int,string> $data list of error messages
	 * @param \Closure(array<int,string> $data): array<int,string> $next
	 *
	 * @return array<int,string>
	 */
	public function handle(array &$data, \Closure $next): array
	{
		if ($this->lycheeVersion->isRelease()) {
			// @codeCoverageIgnoreStart
			$db_ver = $this->lycheeVersion->getDBVersion();
			$file_ver = $this->lycheeVersion->getFileVersion();
			if ($db_ver->toInteger() < $file_ver->toInteger()) {
				$data[] = 'Error: Database is behind file versions. Please apply the migration.';
			}
			// @codeCoverageIgnoreEnd
		}

		return $next($data);
	}
}
