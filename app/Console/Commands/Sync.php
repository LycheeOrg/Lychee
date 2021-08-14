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
	protected $signature = 'lychee:sync {dir : directory to sync} {--album_id=0 : Album ID to import to} {--owner_id=0 : Owner ID of imported photos} {--resync_metadata : Re-sync metadata of existing files}  {--delete_imported : Delete the original files} {--import_via_symlink= : Imports photos from via a symlink instead of copying the files} {--not_skip_duplicates : Don\'t Skip photos and albums if they already exist in the gallery}';

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
		$directory = $this->argument('dir');
		$owner_id = (int) $this->option('owner_id'); // in case no ID provided -> import as root user
		$album_id = (int) $this->option('album_id'); // in case no ID provided -> import to root folder

		// Enable CLI formatting of status
		$exec->statusCLIFormatting = true;
		$exec->memCheck = false;
		$exec->delete_imported = $this->option('delete_imported');
		if (!is_null($this->option('import_via_symlink'))) {
			$exec->import_via_symlink = $this->option('import_via_symlink') === '1';
			if ($exec->import_via_symlink && $exec->delete_imported) {
				$this->error('--delete_imported  and --import_via_symlink flags are conflicting.');
				return 1;
			}
		} else {
			$exec->import_via_symlink = (Configs::get_value('import_via_symlink', '0') === '1');
			if ($exec->import_via_symlink && $exec->delete_imported) {
				$this->error('--delete_imported flag and Config "import_via_symlink" setting are conflicting.');
				$this->info('  Use --import_via_symlink=0 with --delete_imported to overwrite the Config.');
				return 1;
			}
		}
		$exec->skip_duplicates = !$this->option('skip_duplicates');
		$exec->resync_metadata = $this->option('resync_metadata');

		AccessControl::log_as_id($owner_id);

		$this->info('Start syncing.');

		try {
			$exec->do($directory, $album_id);
		} catch (Exception $e) {
			$this->error($e);
		}

		$this->info('Done syncing.');
	}
}
