<?php

namespace App\Actions\Install;

use App\Exceptions\InstallationException;
use Illuminate\Support\Facades\Artisan;

class ApplyMigration
{
	/**
	 * Arrayify a string and append it to $output.
	 *
	 * @param $string
	 * @param array $output
	 *
	 * @return array
	 */
	private function str_to_array($string, array &$output)
	{
		$a = explode("\n", $string);
		foreach ($a as $aa) {
			if ($aa != '') {
				$output[] = $aa;
			}
		}

		return $output;
	}

	/**
	 * @return bool
	 */
	public function migrate(array &$output)
	{
		Artisan::call('view:clear');
		Artisan::call('migrate', ['--force' => true]);
		$this->str_to_array(Artisan::output(), $output);

		/*
		 * We also double check there is no "QueryException" in the output (just to be sure).
		 */
		foreach ($output as $line) {
			if (str_contains($line, 'QueryException')) {
				// @codeCoverageIgnoreStart
				return true;
				// @codeCoverageIgnoreEnd
			}
		}

		return false;
	}

	/**
	 * @return bool
	 *
	 * @throws InstallationException
	 */
	public function keyGenerate(array &$output)
	{
		try {
			Artisan::call('key:generate', ['--force' => true]);
			$this->str_to_array(Artisan::output(), $output);
			// @codeCoverageIgnoreStart
		} catch (\Exception $e) {
			$output[] = $e->getMessage();
			$output[] = 'We could not generate the encryption key.';

			return true;
		}
		// @codeCoverageIgnoreEnd

		// key is generated, we can safely remove that file (in theory)
		$filename = base_path('.NO_SECURE_KEY');
		if (file_exists($filename)) {
			if (is_file($filename)) {
				try {
					unlink($filename);
				} catch (\Throwable $e) {
					throw new InstallationException('Could not remove ' . $filename, $e);
				}
			} else {
				throw new InstallationException('A filesystem object . ' . $filename . ' exists, but is not an ordinary file.');
			}
		}

		return false;
	}
}
