<?php

namespace Installer\Controller;

class MigrationController implements Controller
{
	/**
	 * @return array
	 */
	public function do()
	{
		$output = [];

		$error = $this->migrate($output);
		if (!$error) {
			$error = $this->keyGenerate($output);
		}
		if (!$error) {
			$this->installed($output);
		}

		return ['lines' => $output, 'errors' => $error];
	}

	/**
	 * @return bool|null
	 */
	private function migrate(array &$output)
	{
		$res = 0;
		exec('php artisan migrate', $output, $res);
		/*
		 * It should be 0 if not fail.
		 */
		if ($res != 0) {
			return true;
		}

		/*
		 * We also double check there is no "QueryException" in the output (just to be sure).
		 */
		foreach ($output as $line) {
			if (strpos($line, 'QueryException') !== false) {
				return true;
			}
		}

		return null;
	}

	/**
	 * @return bool|null
	 */
	private function keyGenerate(array &$output)
	{
		$res = 0;
		exec('php artisan key:generate', $output, $res);
		if ($res != 0) {
			return true;
		}

		return null;
	}

	/**
	 * @return string
	 */
	public function view()
	{
		return 'Migrate';
	}

	/**
	 * @param array $output
	 */
	public function installed(array &$output)
	{
		$dateStamp = date('Y-m-d H:i:s');
		$message = 'Lychee INSTALLED on ' . $dateStamp;
		file_put_contents('installed.log', $message);
		$output[] = $message;
		$output[] = 'Created installed.log';
	}
}