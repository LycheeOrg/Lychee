<?php

namespace App\Console\Commands;

use App\Http\Controllers\ImportController;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class Sync extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:sync {dir : directory to sync} {--album_id=0 : Album ID to import to} {--owner_id=0 : Owner ID of imported photos} {--resync_metadata : Re-sync metadata of existing files}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sync a directory to lychee';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$this->info('Start syncing.');
		$directory = $this->argument('dir');
		$owner_id = (int) $this->option('owner_id'); // in case no ID provided -> import as root user
		$album_id = (int) $this->option('album_id'); // in case no ID provided -> import to root folder
		$resync_metadata = $this->option('resync_metadata');
		$delete_imported = false; // we want to sync -> do not delete imported files
		$force_skip_duplicates = true;
		$import_controller = resolve(ImportController::class);

		// Enable CLI formatting of status
		$import_controller->enableCLIStatus();
		// Disable Memory Check
		$import_controller->disableMemCheck();

		Auth::loginUsingId($owner_id);
		// Session::put('UserID', $owner_id);
		// Session::put('login', true);
		try {
			$import_controller->server_exec($directory, $album_id, $delete_imported, $force_skip_duplicates, null, $resync_metadata);
		} catch (Exception $e) {
			$this->error($e);
		}

		$this->info('Done syncing.');
	}
}
