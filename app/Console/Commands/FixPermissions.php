<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Console\Commands;

use App\Actions\Diagnostics\Pipes\Checks\BasicPermissionCheck;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use function Safe\chmod;
use Safe\Exceptions\FilesystemException;
use function Safe\fileowner;
use function Safe\fileperms;
use Symfony\Component\Console\Exception\InvalidArgumentException;

class FixPermissions extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:fix-permissions {--dry-run=1 : Dry run (default is true)}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Fixes the directory permissions (POSIX only; must be run as the user which owns the media files)';

	/**
	 * @var int ID of (POSIX) user which runs this command
	 */
	protected int $effUserId;

	/**
	 * @var bool indicates whether the command shall only report what it would do without actually doing anything
	 */
	protected bool $isDryRun;

	/**
	 * @var int Number of files & folders for which permissions changes are required
	 */
	private int $changesExpected = 0;

	/**
	 * @return int
	 *
	 * @throws InvalidArgumentException
	 */
	public function handle(): int
	{
		$directories = [
			Storage::disk('images')->path(''),
			Storage::disk('symbolic')->path(''),
		];

		if (!extension_loaded('posix')) {
			$this->error('Non-POSIX OS detected: Command unsupported');

			return -1;
		}

		$this->isDryRun = filter_var($this->option('dry-run'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== false;

		clearstatcache(true);
		$this->effUserId = posix_geteuid();

		foreach ($directories as $directory) {
			$this->line(sprintf('Scanning: <info>%s</info>', $directory));
			$this->fixPermissionsRecursively($directory);
		}

		if ($this->isDryRun && $this->changesExpected > 0) {
			$this->line('');
			$this->line('To apply those modifications, run <info>php artisan lychee:fix-permissions --dry-run=0</info>');
		}
		if ($this->isDryRun && $this->changesExpected === 0) {
			$this->line('');
			$this->line('Nothing to fix.');
		}
		$this->warn('This command cannot check for correct group ownership; the web diagnostic may report errors which are not detected by this tool');

		return 0;
	}

	/**
	 * Fixes a directory and its children recursively.
	 */
	private function fixPermissionsRecursively(string $path): void
	{
		try {
			$actualPerm = fileperms($path);

			// `fileperms` also returns the higher bits of the inode mode.
			// Hence, we must AND it with 07777 to only get what we are
			// interested in
			$actualPerm &= 07777;

			$ownerId = fileowner($path);
			$fileType = filetype($path);

			$expectedPerm = match ($fileType) {
				'dir' => BasicPermissionCheck::getConfiguredDirectoryPerm(),
				'file' => BasicPermissionCheck::getConfiguredFilePerm(),
				default => $actualPerm, // we do not care for links and other special files
			};

			if ($expectedPerm !== $actualPerm) {
				$this->warn(
					sprintf('%s has permissions %04o, but should have %04o', $path, $actualPerm, $expectedPerm)
				);

				if ($this->isDryRun) {
					$this->info(sprintf(
						'  => Would change permissions of %s from %04o to %04o', $path, $actualPerm, $expectedPerm
					));
					$this->changesExpected++;
				} else {
					if ($ownerId === $this->effUserId) {
						$this->info(sprintf(
							'  => Changing permissions of %s from %04o to %04o', $path, $actualPerm, $expectedPerm
						));
						chmod($path, $expectedPerm);
					} else {
						$this->error(
							sprintf('Cannot change permissions of %s from %04o to %04o as current user is not the owner', $path, $actualPerm, $expectedPerm)
						);
					}
				}
			}

			if ($fileType === 'dir') {
				$dir = new \DirectoryIterator($path);
				foreach ($dir as $dirEntry) {
					if ($dirEntry->isDir() && !$dirEntry->isDot() || $dirEntry->isFile()) {
						$this->fixPermissionsRecursively($dirEntry->getPathname());
					}
				}
			}
		} catch (FilesystemException) {
			$this->warn(sprintf('Unable to determine permissions for %s' . PHP_EOL, $path));
		} catch (\Exception $e) {
			$this->error($e->getMessage());
		}
	}
}
