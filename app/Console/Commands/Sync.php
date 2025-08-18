<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Console\Commands;

use App\Actions\Import\Exec;
use App\Contracts\Exceptions\ExternalLycheeException;
use App\DTO\ImportMode;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\EmptyFolderException;
use App\Exceptions\InvalidDirectoryException;
use App\Exceptions\UnexpectedException;
use App\Models\Album;
use App\Models\Configs;
use Illuminate\Console\Command;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Exception\ExceptionInterface;

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
		'{dir* : directory to sync} ' . // string[]
		'{--album_id= : Album ID to import to} ' . // string or null
		'{--owner_id=%d : Owner ID of imported photos} ' . // string
		'{--resync_metadata=1 : Re-sync metadata of existing files} ' . // bool
		'{--delete_imported=%s : Delete the original files} ' . // string
		'{--import_via_symlink=%s : Import photos via symlink instead of copying the files} ' . // string
		'{--skip_duplicates=%s : Skip photos and albums if they already exist in the gallery} ' . // string
		'{--delete_missing_photos=%s : Delete photos in the album that are not present in the synced directory} ' . // bool
		'{--delete_missing_albums=%s : Delete albums in the parent album that are not present in the synced directory} ' . // bool
		'{--dry_run=%s : Run the delete photos process but do not make any changes}'; // bool

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sync a directory structure to Lychee, creating albums matching the folder hierarchy';

	public function __construct()
	{
		// Fill signature with default values from user configuration
		try {
			$this->signature = sprintf(
				$this->signature,
				DB::table('users')->where('may_administrate', true)->first()?->id ?? 1,
				Configs::getValueAsString('delete_imported'),
				Configs::getValueAsString('import_via_symlink'),
				Configs::getValueAsString('skip_duplicates'),
				Configs::getValueAsString('sync_delete_missing_photos'),
				Configs::getValueAsString('sync_delete_missing_albums'),
				Configs::getValueAsString('sync_dry_run'),
			);
		} catch (ConfigurationKeyMissingException|QueryException) {
			// Catching this exception is necessary as artisan package:discover
			// is called after each composer installation/update and artisan
			// tries to instantiate every command.
			$this->signature = sprintf($this->signature, 1, '0', '0', '0', '0', '0', '1');
		}
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
		$this->line('');
		try {
			$directories = $this->validateDirectories();
			if ($directories === null) {
				return 1;
			}

			$owner_id = $this->validateOwnerId();
			if ($owner_id === false) {
				return 1;
			}

			$album = $this->validateAlbumId();
			if ($album === false) {
				return 1;
			}

			$import_settings = $this->validateImportSettings();
			if ($import_settings === null) {
				return 1;
			}

			return $this->executeImport($directories, $album, $owner_id, $import_settings);
		} catch (ExceptionInterface $e) {
			throw new UnexpectedException($e);
		}
	}

	/**
	 * Validate the directories provided as arguments.
	 *
	 * @return array|null Array of directory paths or null on validation failure
	 */
	private function validateDirectories(): ?array
	{
		$directories = $this->argument('dir');
		if (!is_array($directories)) {
			$this->error('List of directories not recognized.');

			return null;
		}
		$this->line('Directories to sync:');
		foreach ($directories as $directory) {
			$this->line('  - ' . $directory);
		}

		return $directories;
	}

	/**
	 * Validate the owner ID option.
	 *
	 * @return int Owner ID
	 */
	private function validateOwnerId(): int|false
	{
		$owner_id = (int) $this->option('owner_id');
		if (!DB::table('users')->where('id', $owner_id)->exists()) {
			$this->error('Invalid owner ID provided.');

			return false;
		}

		$this->line('Owner ID: ' . $owner_id);

		return $owner_id;
	}

	/**
	 * Validate the album ID option.
	 *
	 * @return Album|false|null Album object, null if no album ID was provided, or false on validation failure
	 */
	private function validateAlbumId(): Album|false|null
	{
		$album_id = $this->option('album_id');
		if (is_array($album_id)) {
			$this->error('Only one value for album_id is allowed.');

			return false;
		}

		if ($album_id === null) {
			$this->line('No album ID provided, importing to root album.');

			return null;
		}
		/** @var Album|null $album */
		$album = Album::query()->find($album_id);
		if ($album === null) {
			$this->error('Album ID ' . $album_id . ' not found.');

			return false;
		}
		$this->line('Album: ' . $album->title . ' (ID: ' . $album->id . ')');

		return $album;
	}

	/**
	 * Validate the import settings.
	 *
	 * @return ImportMode|null Array of import settings or false on validation failure
	 */
	private function validateImportSettings(): ?ImportMode
	{
		$delete_imported = $this->option('delete_imported') === '1';
		$import_via_symlink = $this->option('import_via_symlink') === '1';
		$skip_duplicates = $this->option('skip_duplicates') === '1';
		$resync_metadata = $this->option('resync_metadata') === '1';

		if ($import_via_symlink && $delete_imported) {
			$this->error('The settings for import via symbolic links and deletion of imported files are conflicting');
			$this->info('  Use --import_via_symlink={0|1} and --delete-imported={0|1} explicitly to apply a conflict-free setting');

			return null;
		}

		$this->line('Import settings:');
		$this->line('  - Delete imported files: ' . $this->yes_no($delete_imported));
		$this->line('  - Import via symlink: ' . $this->yes_no($import_via_symlink));
		$this->line('  - Skip duplicates: ' . $this->yes_no($skip_duplicates));
		$this->line('  - Resync metadata: ' . $this->yes_no($resync_metadata));

		return new ImportMode(
			$delete_imported,
			$skip_duplicates,
			$import_via_symlink,
			$resync_metadata,
		);
	}

	/**
	 * Validate the delete missing photos option.
	 *
	 * @return bool
	 */
	private function validateDeleteMissingPhotos(bool $dry_run): bool
	{
		$delete_missing_photos = $this->option('delete_missing_photos') === '1';
		$this->line('Delete missing photos: ' . $this->yes_no($delete_missing_photos) . ($delete_missing_photos && $dry_run ? ' <fg=#ff0>(dry run)</>' : ''));

		return $delete_missing_photos;
	}

	private function validateDeleteMissingAlbum(bool $dry_run): bool
	{
		$delete_missing_albums = $this->option('delete_missing_albums') === '1';
		$this->line('Delete missing albums: ' . $this->yes_no($delete_missing_albums) . ($delete_missing_albums && $dry_run ? ' <fg=#ff0>(dry run)</>' : ''));

		return $delete_missing_albums;
	}

	/**
	 * Execute the import process.
	 *
	 * @param array      $directories Directories to import
	 * @param Album|null $album       Parent album or null for root
	 * @param int        $owner_id    Owner ID for the imported files
	 * @param ImportMode $import_mode Import settings
	 *
	 * @return int Status code (0 for success)
	 */
	private function executeImport(array $directories, ?Album $album, int $owner_id, ImportMode $import_mode): int
	{
		$dry_run = $this->option('dry_run') === '1';
		$delete_missing_photos = $this->validateDeleteMissingPhotos($dry_run);
		$delete_missing_albums = $this->validateDeleteMissingAlbum($dry_run);

		if (($delete_missing_photos || $delete_missing_albums) && $dry_run) {
			$this->line('<fg=gray>Running in dry run mode, no changes will be made. Rerun with --dry_run=0 to apply changes.</>');
		} elseif ($delete_missing_photos || $delete_missing_albums) {
			$this->error('Running in normal mode, destructive changes will be applied.');
		}

		$this->line('');
		$this->line('');
		if (config('queue.default') === 'sync') {
			$this->warn('Running in sync mode, this may take a while depending on the number of files.');
			$this->warn('Consider setting `QUEUE_CONNECTION=database` in your `.env` and using multiple queue workers (php artisan queue:work) for better performance.');
		} else {
			$this->info('Running in async mode, this will create jobs for each file.');
			$this->info('Make sure you have queue workers running on the side (php artisan queue:work).');
		}
		// Add some spacing for better readability
		$this->line('');
		$this->line('');

		$exec = new Exec(
			import_mode: $import_mode,
			intended_owner_id: $owner_id,
			delete_missing_photos: $delete_missing_photos,
			delete_missing_albums: $delete_missing_albums,
			is_dry_run: $dry_run,
		);

		$this->info('Start tree-based syncing (maintains folder structure).');

		foreach ($directories as $directory) {
			try {
				$exec->do($directory, $album);
			} catch (EmptyFolderException|InvalidDirectoryException $e) {
				return 1;
			} catch (\Exception $e) {
				$this->error($e);
				throw new UnexpectedException($e);
			}
		}

		$this->info('Done tree-based syncing.');

		return 0;
	}

	private function yes_no(bool $value): string
	{
		return $value ? '<fg=#0f0>Yes</>' : '<fg=#f00>No</>';
	}
}
