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
	 * The placeholder %s will be filled by the constructor with the default
	 * value from the user configuration.
	 *
	 * @var string
	 */
	protected $signature =
		'lychee:sync ' .
		'{dir : directory to sync} ' .
		'{--album_id=0 : Album ID to import to} ' .
		'{--owner_id=0 : Owner ID of imported photos} ' .
		'{--resync_metadata : Re-sync metadata of existing files}  ' .
		'{--delete_imported=%s : Delete the original files} ' .
		'{--import_via_symlink=%s : Imports photos from via a symlink instead of copying the files} ' .
		'{--skip_duplicates=%s : Don\'t Skip photos and albums if they already exist in the gallery}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sync a directory to lychee';

	public function __construct()
	{
		// Fill signature with default values from user configuration
		$this->signature = sprintf(
			$this->signature,
			Configs::get_value('delete_imported', '0'),
			Configs::get_value('import_via_symlink', '0'),
			Configs::get_value('skip_duplicates', '0')
		);
		parent::__construct();
	}

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
		$exec->resync_metadata = $this->option('resync_metadata');
		$exec->delete_imported = $this->option('delete_imported') === '1';
		$exec->import_via_symlink = $this->option('import_via_symlink') === '1';
		$exec->skip_duplicates = $this->option('skip_duplicates') === '1';

		if ($exec->import_via_symlink && $exec->delete_imported) {
			$this->error('The settings for import via symbolic links and deletion of imported files are conflicting');
			$this->info('  Use --import_via_symlink={0|1} and --delete-imported={0|1} explicitly to apply a conflict-free setting');

			return 1;
		}

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
