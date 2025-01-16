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
use App\Exceptions\UnexpectedException;
use App\Models\Album;
use App\Models\Configs;
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
		'{dir* : directory to sync} ' . // string[]
		'{--album_id= : Album ID to import to} ' . // string or null
		'{--owner_id=1 : Owner ID of imported photos} ' . // string
		'{--resync_metadata : Re-sync metadata of existing files}  ' . // bool
		'{--delete_imported=%s : Delete the original files} ' . // string
		'{--import_via_symlink=%s : Imports photos from via a symlink instead of copying the files} ' . // string
		'{--skip_duplicates=%s : Skip photos and albums if they already exist in the gallery}'; // string

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Sync a directory to lychee';

	public function __construct()
	{
		// Fill signature with default values from user configuration
		try {
			$this->signature = sprintf(
				$this->signature,
				Configs::getValueAsString('delete_imported'),
				Configs::getValueAsString('import_via_symlink'),
				Configs::getValueAsString('skip_duplicates')
			);
		} catch (ConfigurationKeyMissingException) {
			// Catching this exception is necessary as artisan package:discover
			// is called after each composer installation/update and artisan
			// tries to instantiate every command.
			$this->signature = sprintf($this->signature, '0', '0', '0');
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
		try {
			$directories = $this->argument('dir');
			if (!is_array($directories)) {
				$this->error('List of directories not recognized.');

				return 1;
			}
			$owner_id = (int) $this->option('owner_id'); // in case no ID provided -> import as root user
			$album_id = $this->option('album_id'); // in case no ID provided -> import to root folder
			if (is_array($album_id)) {
				$this->error('Only one value for album_id is allowed.');

				return 1;
			}
			/** @var Album $album */
			$album = $album_id !== null ? Album::query()->findOrFail($album_id) : null; // in case no ID provided -> import to root folder

			$deleteImported = $this->option('delete_imported') === '1';
			$importViaSymlink = $this->option('import_via_symlink') === '1';
			$skipDuplicates = $this->option('skip_duplicates') === '1';
			$resyncMetadata = $this->option('resync_metadata') === true; // ! Because the option is --resync_metadata the return type of $this->option() is already bool.

			if ($importViaSymlink && $deleteImported) {
				$this->error('The settings for import via symbolic links and deletion of imported files are conflicting');
				$this->info('  Use --import_via_symlink={0|1} and --delete-imported={0|1} explicitly to apply a conflict-free setting');

				return 1;
			}

			$exec = new Exec(
				new ImportMode(
					$deleteImported,
					$skipDuplicates,
					$importViaSymlink,
					$resyncMetadata
				),
				$owner_id,
				true,
				0
			);

			$this->info('Start syncing.');

			foreach ($directories as $directory) {
				try {
					$exec->do($directory, $album);
				} catch (\Exception $e) {
					$this->error($e);
				}
			}

			$this->info('Done syncing.');

			return 0;
		} catch (SymfonyConsoleException $e) {
			throw new UnexpectedException($e);
		}
	}
}
