<?php

namespace App\Console\Commands;

use App\Actions\Import\Exec;
use App\Facades\AccessControl;
use App\Models\Configs;
use Exception;
use Illuminate\Console\Command;

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
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle(Exec $exec)
	{
		$this->info('Start syncing.');
		$directory = $this->argument('dir');
		$owner_id = (int) $this->option('owner_id'); // in case no ID provided -> import as root user
		$album_id = (int) $this->option('album_id'); // in case no ID provided -> import to root folder

		// Enable CLI formatting of status
		$exec->statusCLIFormatting = true;
		$exec->memCheck = false;
		$exec->delete_imported = false; // we want to sync -> do not delete imported files
		$exec->import_via_symlink = (Configs::get_value('import_via_symlink', '0') === '1');
		$exec->skip_duplicates = true;
		$exec->resync_metadata = $this->option('resync_metadata');

		AccessControl::log_as_id($owner_id);

		try {
			$exec->do($directory, $album_id);
		} catch (Exception $e) {
			$this->error($e);
		}

		$this->info('Done syncing.');
	}
}
