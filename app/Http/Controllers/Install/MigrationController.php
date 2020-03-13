<?php

namespace App\Http\Controllers\Install;

use App\ControllerFunctions\Install\ApplyMigration;
use App\Http\Controllers\Controller;

class MigrationController extends Controller
{
	/**
	 * @var ApplyMigration
	 */
	protected $applyMigration;

	public function __construct(ApplyMigration $applyMigration)
	{
		$this->applyMigration = $applyMigration;
	}

	/**
	 * @return array
	 */
	public function view()
	{
		$output = [];

		$error = $this->applyMigration->migrate($output);
		$output[] = '';
		if (!$error) {
			$error = $this->applyMigration->keyGenerate($output);
		}
		$output[] = '';
		if (!$error) {
			$this->installed($output);
		}
		$error = $error ? true : null;

		return view('install.migrate', [
			'title' => 'Lychee-installer',
			'step' => 4,
			'lines' => $output,
			'errors' => $error, ]
		);
	}

	/**
	 * @param array $output
	 */
	public function installed(array &$output)
	{
		$dateStamp = date('Y-m-d H:i:s');
		$message = 'Lychee INSTALLED on ' . $dateStamp;
		file_put_contents(base_path('installed.log'), $message);
		$output[] = $message;
		$output[] = 'Created installed.log';
	}
}