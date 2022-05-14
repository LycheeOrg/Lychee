<?php

namespace App\Console\Commands;

use App\Actions\Import\Exec;
use App\Actions\Photo\Strategies\ImportMode;
use App\Contracts\ExternalLycheeException;
use App\Exceptions\UnexpectedException;
use App\Facades\AccessControl;
use App\Models\Album;
use App\Models\Configs;
use Exception;
use Illuminate\Console\Command;
use Symfony\Component\Console\Exception\ExceptionInterface as SymfonyConsoleException;

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
		'{--album_id= : Album ID to import to} ' .
		'{--owner_id=0 : Owner ID of imported photos} ' .
		'{--resync_metadata : Re-sync metadata of existing files}  ' .
		'{--delete_imported=%s : Delete the original files} ' .
		'{--import_via_symlink=%s : Imports photos from via a symlink instead of copying the files} ' .
		'{--skip_duplicates=%s : Don\'t skip photos and albums if they already exist in the gallery}';

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
	 * @return int
	 *
	 * @throws ExternalLycheeException
	 */
	public function handle(): int
	{
		try {
			$directory = $this->argument('dir');
			$owner_id = (int) $this->option('owner_id'); // in case no ID provided -> import as root user
			$album_id = ((string) $this->option('album_id')) ?: null; // in case no ID provided -> import to root folder
			/** @var Album $album */
			$album = $album_id ? Album::query()->findOrFail($album_id) : null; // in case no ID provided -> import to root folder

			$deleteImported = $this->option('delete_imported') === '1';
			$importViaSymlink = $this->option('import_via_symlink') === '1';

			if ($importViaSymlink && $deleteImported) {
				$this->error('The settings for import via symbolic links and deletion of imported files are conflicting');
				$this->info('  Use --import_via_symlink={0|1} and --delete-imported={0|1} explicitly to apply a conflict-free setting');

				return 1;
			}

			$exec = new Exec(
				new ImportMode(
					$deleteImported,
					$this->option('import_via_symlink') === '1',
					$importViaSymlink,
					$this->option('resync_metadata')
				),
				true,
				0
			);

			AccessControl::log_as_id($owner_id);

			$this->info('Start syncing.');

			try {
				$exec->do($directory, $album);
			} catch (Exception $e) {
				$this->error($e);
			}

			$this->info('Done syncing.');

			return 0;
		} catch (SymfonyConsoleException $e) {
			throw new UnexpectedException($e);
		}
	}
}
