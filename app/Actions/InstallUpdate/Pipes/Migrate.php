<?php

namespace App\Actions\InstallUpdate\Pipes;

use Illuminate\Support\Facades\Artisan;

class Migrate extends AbstractUpdaterPipe
{
	/**
	 * Runs the migration via the Artisan Facade.
	 *
	 * **TODO:** Consolidate with {@link \App\Actions\InstallUpdate\ApplyMigration::migrate()}.
	 *
	 * **ATTENTION:** This method serves the same purpose as
	 * `ApplyMigration::migrate()`.
	 * The whole code around installation/upgrade/migration should
	 * thoroughly be revised an refactored.
	 *
	 * {@inheritDoc}
	 */
	public function handle(array &$output, \Closure $next): array
	{
		Artisan::call('migrate', ['--force' => true]);

		$a = explode("\n", Artisan::output());
		foreach ($a as $aa) {
			if ($aa !== '') {
				$output[] = $aa;
			}
		}

		return $next($output);
	}
}