<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Constants\FileSystem;
use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\Enum\StorageDiskType;
use App\Exceptions\Handler;
use App\Exceptions\Internal\InvalidConfigOption;
use App\Facades\Helpers;
use App\Models\Configs;
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
	public const READ_WRITE_ALL = 07777;

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
			$group_ids_or_false = posix_getgroups();
			// @codeCoverageIgnoreStart
		} catch (PosixException) {
			$data[] = DiagnosticData::error('Could not determine groups of process', self::class);

			return;
		}
		// @codeCoverageIgnoreEnd
		$this->groupIDs = $group_ids_or_false;
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
			Storage::disk(FileSystem::SYMLINK),
			Storage::disk(FileSystem::IMAGE_JOBS),
			Storage::disk(FileSystem::IMAGE_UPLOAD),
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
		$p = Storage::disk(FileSystem::DIST)->path('user.css');
		if (!Helpers::hasPermissions($p)) {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::warn(sprintf("'%s' does not exist or has insufficient read/write privileges.", $this->anonymize($p)), self::class);

			$p = Storage::disk(FileSystem::DIST)->path('');
			if (!Helpers::hasPermissions($p)) {
				$data[] = DiagnosticData::warn(sprintf("'%s' has insufficient read/write privileges.", $this->anonymize($p)), self::class);
			}
			// @codeCoverageIgnoreEnd
		}
		if (Configs::getValueAsBool('disable_recursive_permission_check')) {
			$data[] = DiagnosticData::info('Full directory permission check is disabled', self::class);
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
				$actual_perm = fileperms($path);
				// @codeCoverageIgnoreStart
			} catch (FilesystemException) {
				$data[] = DiagnosticData::warn(sprintf('Unable to determine permissions for %s', $this->anonymize($path)), self::class);

				return;
			}
			// @codeCoverageIgnoreEnd

			// `fileperms` also returns the higher bits of the inode mode.
			// Hence, we must AND it with 07777 to only get what we are
			// interested in
			$actual_perm &= self::READ_WRITE_ALL;
			$owning_group_id_or_false = filegroup($path);
			if ($owning_group_id_or_false !== false) {
				try {
					$owning_group_name_or_false = posix_getgrgid($owning_group_id_or_false);
					// @codeCoverageIgnoreStart
				} catch (PosixException) {
					$owning_group_name_or_false = false;
				}
			// @codeCoverageIgnoreEnd
			} else {
				$owning_group_name_or_false = false;
			}
			/** @var string $owningGroupName */
			$owning_group_name = $owning_group_name_or_false === false ? '<unknown>' : $owning_group_name_or_false['name'];
			$expected_perm = self::getConfiguredDirectoryPerm();

			if (!in_array($owning_group_id_or_false, $this->groupIDs, true)) {
				// @codeCoverageIgnoreStart
				$this->numOwnerIssues++;
				if ($this->numOwnerIssues <= self::MAX_ISSUE_REPORTS_PER_TYPE) {
					$data[] = DiagnosticData::warn(sprintf('%s is owned by group %s, but should be owned by one out of %s', $this->anonymize($path), $owning_group_name, $this->groupNames), self::class);
				}
				// @codeCoverageIgnoreEnd
			}

			if ($expected_perm !== $actual_perm) {
				// @codeCoverageIgnoreStart
				$this->numPermissionIssues++;
				if ($this->numPermissionIssues <= self::MAX_ISSUE_REPORTS_PER_TYPE) {
					$data[] = DiagnosticData::warn(sprintf('%s has permissions %04o, but should have %04o', $this->anonymize($path), $actual_perm, $expected_perm), self::class);
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
			if (Configs::getValueAsBool('disable_recursive_permission_check')) {
				return;
			}
			foreach ($dir as $dir_entry) {
				if ($dir_entry->isDir() && !$dir_entry->isDot()) {
					$this->checkDirectoryPermissionsRecursively($dir_entry->getPathname(), $data);
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
