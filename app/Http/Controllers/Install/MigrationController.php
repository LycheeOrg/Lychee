<?php

namespace App\Http\Controllers\Install;

use App\Actions\Install\ApplyMigration;
use App\Exceptions\InstallationFailedException;
use App\Exceptions\Internal\FrameworkException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use function Safe\date;
use function Safe\file_put_contents;

/**
 * Class MigrationController.
 *
 * Most likely, the concerns of this controller should be decomposed into two.
 * Currently, this {@link MigrationController::view} does not only migrate the
 * DB but also generates a new API key.
 * The latter is only required for fresh installations, but not for DB
 * migrations after an upgrade.
 * This coupling of two logically, separate steps makes it impossible to use
 * this controller for migration after an upgrade.
 * See {@link \App\Http\Controllers\Administration\UpdateController} for more
 * information.
 *
 * TODO: Revise and refactor the whole logic around installation/upgrade/migration.
 */
class MigrationController extends Controller
{
	protected ApplyMigration $applyMigration;

	public function __construct(ApplyMigration $applyMigration)
	{
		$this->applyMigration = $applyMigration;
	}

	/**
	 * Migrates the Lychee DB and generates a new API key.
	 *
	 * **TODO:** Consolidate with {@link \App\Http\Controllers\Administration\UpdateController::migrate()}.
	 *
	 * **ATTENTION:** This method serves a somewhat similar purpose as
	 * `UpdateController::migrate()` except that the latter does not generate
	 * a new API key.
	 * Also note, that this method internally uses
	 * {@link ApplyMigration::migrate()} while `UpdateController::migrate`
	 * uses {@link \App\Actions\Update\Apply::migrate()}.
	 * However, both methods are very similar, too.
	 * The whole code around installation/upgrade/migration should
	 * thoroughly be revised an refactored.
	 *
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
