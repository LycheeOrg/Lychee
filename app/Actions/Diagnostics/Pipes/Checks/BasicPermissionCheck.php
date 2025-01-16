<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\Enum\StorageDiskType;
use App\Exceptions\Handler;
use App\Exceptions\Internal\InvalidConfigOption;
use App\Facades\Helpers;
use App\Http\Controllers\Gallery\PhotoController;
use App\Image\Files\ProcessableJobFile;
use App\Models\SymLink;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\PosixException;
use function Safe\fileperms;
use function Safe\posix_getgrgid;
use function Safe\posix_getgroups;

/**
 * We check that the folders are with the correct permissions.
 * Mostly read write.
 *
 * Unhappy flows with posix missing are ignored from coverage.
 */
class BasicPermissionCheck implements DiagnosticPipe
{
	public const MAX_ISSUE_REPORTS_PER_TYPE = 5;

	/**
	 * @var int[] IDs of all (POSIX) groups to which the process belongs
	 */
	protected array $groupIDs;

	/**
	 * @var string Comma-separated list of names of (POSIX) groups to which the process belongs
	 */
	protected string $groupNames;

	protected int $numOwnerIssues;

	protected int $numPermissionIssues;

	protected int $numAccessIssues;

	/**
	 * @var array<int,string> List of real paths to be anonymized
	 */
	protected array $realPaths = [];

	/**
	 * @var array<int,string> Matching list of anonymized paths
	 */
	protected array $anonymizePaths = [];

	/**
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		$this->folders($data);
		$this->userCSS($data);

		return $next($data);
	}

	/**
	 * Check all the folders with the correct permissions.
	 *
	 * @param DiagnosticData[] $data
	 *
	 * @return void
	 */
	public function folders(array &$data): void
	{
		if (!extension_loaded('posix')) {
			// @codeCoverageIgnoreStart
			return;
			// @codeCoverageIgnoreEnd
		}

		clearstatcache(true);
		$this->numOwnerIssues = 0;
		$this->numPermissionIssues = 0;
		$this->numAccessIssues = 0;
		try {
			$groupIDsOrFalse = posix_getgroups();
			// @codeCoverageIgnoreStart
		} catch (PosixException) {
			$data[] = DiagnosticData::error('Could not determine groups of process', self::class);

			return;
		}
		// @codeCoverageIgnoreEnd
		$this->groupIDs = $groupIDsOrFalse;
		$this->groupIDs[] = posix_getegid();
		$this->groupIDs[] = posix_getgid();
		$this->groupIDs = array_unique($this->groupIDs);
		$this->groupNames = implode(', ', array_map(
			function (int $gid): string {
				try {
					return posix_getgrgid($gid)['name'];
					// @codeCoverageIgnoreStart
				} catch (PosixException) {
					return '<unknown>';
				}
				// @codeCoverageIgnoreEnd
			},
			$this->groupIDs
		));

		$disks = [
			Storage::disk(StorageDiskType::LOCAL->value),
			Storage::disk(SymLink::DISK_NAME),
			Storage::disk(ProcessableJobFile::DISK_NAME),
			Storage::disk(PhotoController::DISK_NAME),
		];

		foreach ($disks as $disk) {
			if ($disk->getAdapter() instanceof LocalFilesystemAdapter) {
				$this->checkDirectoryPermissionsRecursively($disk->path(''), $data);
			}
		}

		if ($this->numOwnerIssues > self::MAX_ISSUE_REPORTS_PER_TYPE) {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::warn(sprintf('%d more directories with wrong owner', $this->numOwnerIssues - self::MAX_ISSUE_REPORTS_PER_TYPE), self::class);
			// @codeCoverageIgnoreEnd
		}
		if ($this->numPermissionIssues > self::MAX_ISSUE_REPORTS_PER_TYPE) {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::warn(sprintf('%d more directories with wrong permissions', $this->numPermissionIssues - self::MAX_ISSUE_REPORTS_PER_TYPE), self::class);
			// @codeCoverageIgnoreEnd
		}
		if ($this->numAccessIssues > self::MAX_ISSUE_REPORTS_PER_TYPE) {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::warn(sprintf('%d more inaccessible directories', $this->numAccessIssues - self::MAX_ISSUE_REPORTS_PER_TYPE), self::class);
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * Check if user.css has the correct permissions.
	 *
	 * @param DiagnosticData[] $data
	 *
	 * @return void
	 */
	public function userCSS(array &$data): void
	{
		$p = Storage::disk('dist')->path('user.css');
		if (!Helpers::hasPermissions($p)) {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::warn(sprintf("'%s' does not exist or has insufficient read/write privileges.", $this->anonymize($p)), self::class);

			$p = Storage::disk('dist')->path('');
			if (!Helpers::hasPermissions($p)) {
				$data[] = DiagnosticData::warn(sprintf("'%s' has insufficient read/write privileges.", $this->anonymize($p)), self::class);
			}
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * Check permissions of (local) image directories.
	 *
	 * For efficiency reasons only the directory permissions are checked,
	 * not the permissions of every single file.
	 *
	 * @param string           $path the path of the directory or file to check
	 * @param DiagnosticData[] $data the list of errors to append to
	 *
	 * @noinspection PhpComposerExtensionStubsInspection
	 */
	private function checkDirectoryPermissionsRecursively(string $path, array &$data): void
	{
		try {
			if (!is_dir($path)) {
				return;
			}

			try {
				$actualPerm = fileperms($path);
				// @codeCoverageIgnoreStart
			} catch (FilesystemException) {
				$data[] = DiagnosticData::warn(sprintf('Unable to determine permissions for %s', $this->anonymize($path)), self::class);

				return;
			}
			// @codeCoverageIgnoreEnd

			// `fileperms` also returns the higher bits of the inode mode.
			// Hence, we must AND it with 07777 to only get what we are
			// interested in
			$actualPerm &= 07777;
			$owningGroupIdOrFalse = filegroup($path);
			if ($owningGroupIdOrFalse !== false) {
				try {
					$owningGroupNameOrFalse = posix_getgrgid($owningGroupIdOrFalse);
					// @codeCoverageIgnoreStart
				} catch (PosixException) {
					$owningGroupNameOrFalse = false;
				}
			// @codeCoverageIgnoreEnd
			} else {
				$owningGroupNameOrFalse = false;
			}
			/** @var string $owningGroupName */
			$owningGroupName = $owningGroupNameOrFalse === false ? '<unknown>' : $owningGroupNameOrFalse['name'];
			$expectedPerm = self::getConfiguredDirectoryPerm();

			if (!in_array($owningGroupIdOrFalse, $this->groupIDs, true)) {
				// @codeCoverageIgnoreStart
				$this->numOwnerIssues++;
				if ($this->numOwnerIssues <= self::MAX_ISSUE_REPORTS_PER_TYPE) {
					$data[] = DiagnosticData::warn(sprintf('%s is owned by group %s, but should be owned by one out of %s', $this->anonymize($path), $owningGroupName, $this->groupNames), self::class);
				}
				// @codeCoverageIgnoreEnd
			}

			if ($expectedPerm !== $actualPerm) {
				// @codeCoverageIgnoreStart
				$this->numPermissionIssues++;
				if ($this->numPermissionIssues <= self::MAX_ISSUE_REPORTS_PER_TYPE) {
					$data[] = DiagnosticData::warn(sprintf('%s has permissions %04o, but should have %04o', $this->anonymize($path), $actualPerm, $expectedPerm), self::class);
				}
				// @codeCoverageIgnoreEnd
			}

			if (!is_writable($path) || !is_readable($path)) {
				// @codeCoverageIgnoreStart
				$this->numAccessIssues++;
				if ($this->numAccessIssues <= self::MAX_ISSUE_REPORTS_PER_TYPE) {
					$problem = match (true) {
						(!is_writable($path) && !is_readable($path)) => 'neither readable nor writable',
						!is_writable($path) => 'not writable',
						!is_readable($path) => 'not readable',
						default => '',
					};
					$data[] = DiagnosticData::error(sprintf('%s is %s by %s', $this->anonymize($path), $problem, $this->groupNames), self::class);
				}
				// @codeCoverageIgnoreEnd
			}

			$dir = new \DirectoryIterator($path);
			foreach ($dir as $dirEntry) {
				if ($dirEntry->isDir() && !$dirEntry->isDot()) {
					$this->checkDirectoryPermissionsRecursively($dirEntry->getPathname(), $data);
				}
			}
			// @codeCoverageIgnoreStart
		} catch (\Exception $e) {
			$data[] = DiagnosticData::error($e->getMessage(), self::class);
			Handler::reportSafely($e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * @throws InvalidConfigOption
	 */
	public static function getConfiguredDirectoryPerm(): int
	{
		return self::getConfiguredPerm('dir');
	}

	/**
	 * @throws InvalidConfigOption
	 */
	public static function getConfiguredFilePerm(): int
	{
		return self::getConfiguredPerm('file');
	}

	/**
	 * @param string $type either 'dir' or 'file'
	 *
	 * @return int
	 *
	 * @phpstan-param 'dir'|'file' $type
	 *
	 * @throws InvalidConfigOption
	 */
	private static function getConfiguredPerm(string $type): int
	{
		try {
			$visibility = (string) config(sprintf('filesystems.disks.%s.visibility', StorageDiskType::LOCAL->value));
			if ($visibility === '') {
				// @codeCoverageIgnoreStart
				throw new InvalidConfigOption('File/directory visibility not configured');
				// @codeCoverageIgnoreEnd
			}

			$perm = (int) config(sprintf('filesystems.disks.%s.permissions.%s.%s', StorageDiskType::LOCAL->value, $type, $visibility));
			if ($perm === 0) {
				// @codeCoverageIgnoreStart
				throw new InvalidConfigOption('Configured file/directory permission is invalid');
				// @codeCoverageIgnoreEnd
			}

			return $perm;
			// @codeCoverageIgnoreStart
		} catch (ContainerExceptionInterface|BindingResolutionException|NotFoundExceptionInterface $e) {
			throw new InvalidConfigOption('Could not read configuration for file/directory permission', $e);
		}
		// @codeCoverageIgnoreEnd
	}

	private function anonymize(string $path): string
	{
		if (count($this->anonymizePaths) === 0) {
			$this->realPaths[] = public_path();
			$this->anonymizePaths[] = Helpers::censor(public_path(), 0.2);
			$this->realPaths[] = storage_path();
			$this->anonymizePaths[] = Helpers::censor(storage_path(), 0.4);
			$this->realPaths[] = config('filesystems.disks.images.root');
			$this->anonymizePaths[] = Helpers::censor(config('filesystems.disks.images.root'), 0.2);
		}

		return str_replace($this->realPaths, $this->anonymizePaths, $path);
	}
}
