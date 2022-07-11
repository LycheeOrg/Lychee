<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use function Safe\chmod;
use function Safe\fileowner;
use function Safe\sprintf;
use Symfony\Component\Console\Exception\InvalidArgumentException;

class FixPermissions extends Command
{
	/**
	 * Image directories must be group-writeable and have the special
	 * `set gid` bit.
	 *
	 * Lychee provides different ways how image files can be added or deleted:
	 * either via the web interface or via console commands such as
	 * `artisan lychee:sync` or `artisan lychee:ghostbuster`.
	 * Usually, the user (process owner) who runs the web server and the
	 * user who runs console commands a different.
	 * This might lead to unfortunate file permission problems such that
	 * the images added via the CLI cannot be deleted via the web UI and
	 * vice versa.
	 *
	 * In order to mitigate the effects the image directories are made
	 * group writable.
	 * Moreover, we set the special `set gid` bit.
	 * For directories, this special bit ensure that newly creates files and
	 * sub-directories get the group of their parent directory and not the
	 * group of the running process.
	 */
	public const MIN_DIRECTORY_PERMS = 02770;

	public const MAX_DIRECTORY_PERMS = 02775;

	public const DEFAULT_DIRECTORY_PERMS = self::MAX_DIRECTORY_PERMS;

	public const MIN_FILE_PERMS = 00660;

	public const MAX_FILE_PERMS = 00664;

	public const DEFAULT_FILE_PERMS = self::MAX_FILE_PERMS;

	public const DIRECTORIES = [
		'public/uploads',
		'public/sym',
	];

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'lychee:fix-permissions {dry-run=1 : Dry run (default is true)}';

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
	 * @return int
	 *
	 * @throws InvalidArgumentException
	 */
	public function handle(): int
	{
		if (!extension_loaded('posix')) {
			$this->error('Non-POSIX OS detected: Command unsupported');

			return -1;
		}

		$this->isDryRun = filter_var($this->argument('dry-run'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) !== false;

		clearstatcache(true);
		$this->effUserId = posix_geteuid();

		foreach (self::DIRECTORIES as $directory) {
			$this->line(sprintf('Scanning: <info>%s</info>', $directory));
			$this->fixPermissionsRecursively($directory);
		}

		return 0;
	}

	/**
	 * Fixes a directory and its children recursively.
	 */
	private function fixPermissionsRecursively(string $path): void
	{
		try {
			$actualPerm = fileperms($path);
			if ($actualPerm === false) {
				$this->warn(sprintf('Unable to determine permissions for %s' . PHP_EOL, $path));

				return;
			}

			// `fileperms` also returns the higher bits of the inode mode.
			// Hence, we must AND it with 07777 to only get what we are
			// interested in
			$actualPerm &= 07777;

			$ownerId = fileowner($path);
			$fileType = filetype($path);

			$minPerms = match ($fileType) {
				'dir' => self::MIN_DIRECTORY_PERMS,
				'file' => self::MIN_FILE_PERMS,
				default => 00000, // we do not care for links and other special files
			};
			$maxPerms = match ($fileType) {
				'dir' => self::MAX_DIRECTORY_PERMS,
				'file' => self::MAX_FILE_PERMS,
				default => 07777, // we do not care for links and other special files
			};

			$expectedPerm = ($actualPerm | $minPerms) & $maxPerms;

			if ($expectedPerm !== $actualPerm) {
				$this->warn(
					sprintf('%s has permissions %04o, but should have %04o at least and %04o at most', $path, $actualPerm, $minPerms, $maxPerms)
				);

				if ($this->isDryRun) {
					$this->info(sprintf(
						'Would change permissions of %s from %04o to %04o', $path, $actualPerm, $expectedPerm
					));
				} else {
					if ($ownerId === $this->effUserId) {
						$this->info(sprintf(
							'Changing permissions of %s from %04o to %04o', $path, $actualPerm, $expectedPerm
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
		} catch (\Exception $e) {
			$this->error($e->getMessage());
		}
	}
}
