<?php

namespace App\ControllerFunctions\Install;

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
		Artisan::call('migrate', ['--force' => true]);
		$this->str_to_array(Artisan::output(), $output);

		/*
		 * We also double check there is no "QueryException" in the output (just to be sure).
		 */
		foreach ($output as $line) {
			if (strpos($line, 'QueryException') !== false) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function keyGenerate(array &$output)
	{

		Artisan::call('key:generate', ['--force' => true]);
		$this->str_to_array(Artisan::output(), $output);

		return false;
	}
}