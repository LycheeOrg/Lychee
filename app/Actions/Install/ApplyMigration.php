<?php

namespace App\Actions\Install;

use App\Exceptions\InstallationFailedException;
use App\Exceptions\Internal\FrameworkException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Artisan;

class ApplyMigration
{
	/**
	 * Arrayify a string and append it to $output.
	 *
	 * @param string   $string message text which each message separated by newline
	 * @param string[] $output list of messages
	 *
	 * @return void
	 */
	private function str_to_array(string $string, array &$output): void
	{
		$a = explode("\n", $string);
		foreach ($a as $aa) {
			if ($aa != '') {
				$output[] = $aa;
			}
		}
	}

	/**
	 * Runs the migration via the Artisan Facade.
	 *
	 * **TODO:** Consolidate with {@link \App\Actions\Update\Apply::migrate()}.
	 *
	 * **ATTENTION:** This method serves the same purpose as
	 * `ApplyMigration::migrate()`.
	 * The whole code around installation/upgrade/migration should
	 * thoroughly be revised an refactored.
	 *
	 * @param string[] $output list of messages
	 *
	 * @return void
	 *
	 * @throws InstallationFailedException
	 */
	public function migrate(array &$output): void
	{
		Artisan::call('view:clear');
		Artisan::call('migrate', ['--force' => true]);
		$this->str_to_array(Artisan::output(), $output);

		/*
		 * We check there is no "QueryException" in the output (just to be sure).
		 */
		foreach ($output as $line) {
			if (str_contains($line, 'QueryException')) {
				throw new InstallationFailedException('DB migration failed: ' . $line);
			}
		}
	}

	/**
	 * @param string[] $output list of messages
	 *
	 * @return void
	 *
	 * @throws InstallationFailedException
	 * @throws FrameworkException
	 */
	public function keyGenerate(array &$output): void
	{
		try {
			Artisan::call('key:generate', ['--force' => true]);
			$this->str_to_array(Artisan::output(), $output);
			if (!str_contains(end($output), 'Application key set successfully')) {
				$output[] = 'We could not generate the encryption key.';
				throw new InstallationFailedException('Could not generate encryption key');
			}

			// key is generated, we can safely remove that file (in theory)
			$filename = base_path('.NO_SECURE_KEY');
			if (file_exists($filename)) {
				if (is_file($filename)) {
					unlink($filename);
				} else {
					throw new InstallationFailedException('A filesystem object . ' . $filename . ' exists, but is not an ordinary file.');
				}
			}
		} catch (\ErrorException $e) {
			// possibly thrown by `unlink`
			$output[] = $e->getMessage();
			$output[] = 'Could not remove file `.NO_SECURE_KEY`.';
			throw new InstallationFailedException('Could not remove file `.NO_SECURE_KEY`', $e);
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s container component', $e);
		}
	}
}
