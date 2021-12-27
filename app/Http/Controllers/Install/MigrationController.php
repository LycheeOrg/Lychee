<?php

namespace App\Http\Controllers\Install;

use App\Actions\Install\ApplyMigration;
use App\Exceptions\InstallationFailedException;
use App\Exceptions\Internal\FrameworkException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

class MigrationController extends Controller
{
	protected ApplyMigration $applyMigration;

	public function __construct(ApplyMigration $applyMigration)
	{
		$this->applyMigration = $applyMigration;
	}

	/**
	 * @return View
	 *
	 * @throws FrameworkException
	 */
	public function view(): View
	{
		$output = [];
		$hasErrors = false;
		try {
			$this->applyMigration->migrate($output);
			$output[] = '';
			$this->applyMigration->keyGenerate($output);
			$output[] = '';
			$this->installed($output);
		} catch (InstallationFailedException) {
			$hasErrors = true;
		}

		try {
			return view('install.migrate', [
				'title' => 'Lychee-installer',
				'step' => 4,
				'lines' => $output,
				'errors' => $hasErrors,
			]);
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s view component', $e);
		}
	}

	/**
	 * @param string[] $output list of messages
	 *
	 * @return void
	 *
	 * @throws FrameworkException
	 */
	public function installed(array &$output): void
	{
		try {
			$dateStamp = date('Y-m-d H:i:s');
			$message = 'Lychee INSTALLED on ' . $dateStamp;
			file_put_contents(base_path('installed.log'), $message);
			$output[] = $message;
			$output[] = 'Created installed.log';
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s container component', $e);
		}
	}
}
