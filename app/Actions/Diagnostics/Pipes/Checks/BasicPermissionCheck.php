<?php

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\Contracts\Models\AbstractSizeVariantNamingStrategy;
use App\Exceptions\Handler;
use App\Exceptions\Internal\InvalidConfigOption;
use App\Facades\Helpers;
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
	 * @param array<int,string> $data
	 *
	 * @return void
	 */
	public function folders(array &$data): void
	{
		if (!extension_loaded('posix')) {
			return;
		}

		clearstatcache(true);
		$this->numOwnerIssues = 0;
		$this->numPermissionIssues = 0;
		$this->numAccessIssues = 0;
		try {
			$groupIDsOrFalse = posix_getgroups();
		} catch (PosixException) {
			$data[] = 'Error: Could not determine groups of process';

			return;
		}
		$this->groupIDs = $groupIDsOrFalse;
		$this->groupIDs[] = posix_getegid();
		$this->groupIDs[] = posix_getgid();
		$this->groupIDs = array_unique($this->groupIDs);
		$this->groupNames = implode(', ', array_map(
			function (int $gid): string {
				try {
					return posix_getgrgid($gid)['name'];
				} catch (PosixException) {
					return '<unknown>';
				}
			},
			$this->groupIDs
		));

		$disks = [
			AbstractSizeVariantNamingStrategy::getImageDisk(),
			Storage::disk(SymLink::DISK_NAME),
		];

		foreach ($disks as $disk) {
			if ($disk->getAdapter() instanceof LocalFilesystemAdapter) {
				$this->checkDirectoryPermissionsRecursively($disk->path(''), $data);
			}
		}

		if ($this->numOwnerIssues > self::MAX_ISSUE_REPORTS_PER_TYPE) {
			$data[] = sprintf('Warning: %d more directories with wrong owner', $this->numOwnerIssues - self::MAX_ISSUE_REPORTS_PER_TYPE);
		}
		if ($this->numPermissionIssues > self::MAX_ISSUE_REPORTS_PER_TYPE) {
			$data[] = sprintf('Warning: %d more directories with wrong permissions', $this->numPermissionIssues - self::MAX_ISSUE_REPORTS_PER_TYPE);
		}
		if ($this->numAccessIssues > self::MAX_ISSUE_REPORTS_PER_TYPE) {
			$data[] = sprintf('Warning: %d more inaccessible directories', $this->numAccessIssues - self::MAX_ISSUE_REPORTS_PER_TYPE);
		}
	}

	/**
	 * Check if user.css has the correct permissions.
	 *
	 * @param array<int,string> $data
	 *
	 * @return void
	 */
	public function userCSS(array &$data): void
	{
		$p = Storage::disk('dist')->path('user.css');
		if (!Helpers::hasPermissions($p)) {
			$data[] = "Warning: '" . $p . "' does not exist or has insufficient read/write privileges.";
			$p = Storage::disk('dist')->path('');
			if (!Helpers::hasPermissions($p)) {
				$data[] = "Warning: '" . $p . "' has insufficient read/write privileges.";
			}
		}
	}

	/**
	 * Check permissions of (local) image directories.
	 *
	 * For efficiency reasons only the directory permissions are checked,
	 * not the permissions of every single file.
	 *
	 * @param string   $path the path of the directory or file to check
	 * @param string[] $data the list of errors to append to
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
			} catch (FilesystemException) {
				$data[] = sprintf('Warning: Unable to determine permissions for %s' . PHP_EOL, $path);

				return;
			}

			// `fileperms` also returns the higher bits of the inode mode.
			// Hence, we must AND it with 07777 to only get what we are
			// interested in
			$actualPerm &= 07777;
			$owningGroupIdOrFalse = filegroup($path);
			if ($owningGroupIdOrFalse !== false) {
				try {
					$owningGroupNameOrFalse = posix_getgrgid($owningGroupIdOrFalse);
				} catch (PosixException) {
					$owningGroupNameOrFalse = false;
				}
			} else {
				$owningGroupNameOrFalse = false;
			}
			/** @var string $owningGroupName */
			$owningGroupName = $owningGroupNameOrFalse === false ? '<unknown>' : $owningGroupNameOrFalse['name'];
			$expectedPerm = self::getConfiguredDirectoryPerm();

			if (!in_array($owningGroupIdOrFalse, $this->groupIDs, true)) {
				$this->numOwnerIssues++;
				if ($this->numOwnerIssues <= self::MAX_ISSUE_REPORTS_PER_TYPE) {
					$data[] = sprintf('Warning: %s is owned by group %s, but should be owned by one out of %s', $path, $owningGroupName, $this->groupNames);
				}
			}

			if ($expectedPerm !== $actualPerm) {
				$this->numPermissionIssues++;
				if ($this->numPermissionIssues <= self::MAX_ISSUE_REPORTS_PER_TYPE) {
					$data[] = sprintf(
						'Warning: %s has permissions %04o, but should have %04o',
						$path,
						$actualPerm,
						$expectedPerm
					);
				}
			}

			if (!is_writable($path) || !is_readable($path)) {
				$this->numAccessIssues++;
				if ($this->numAccessIssues <= self::MAX_ISSUE_REPORTS_PER_TYPE) {
					$problem = match (true) {
						(!is_writable($path) && !is_readable($path)) => 'neither readable nor writable',
						!is_writable($path) => 'not writable',
						!is_readable($path) => 'not readable',
						default => ''
					};
					$data[] = sprintf('Error: %s is %s by %s', $path, $problem, $this->groupNames);
				}
			}

			$dir = new \DirectoryIterator($path);
			foreach ($dir as $dirEntry) {
				if ($dirEntry->isDir() && !$dirEntry->isDot()) {
					$this->checkDirectoryPermissionsRecursively($dirEntry->getPathname(), $data);
				}
			}
		} catch (\Exception $e) {
			$data[] = 'Error: ' . $e->getMessage();
			Handler::reportSafely($e);
		}
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
			$visibility = (string) config(sprintf('filesystems.disks.%s.visibility', AbstractSizeVariantNamingStrategy::IMAGE_DISK_NAME));
			if ($visibility === '') {
				throw new InvalidConfigOption('File/directory visibility not configured');
			}

			$perm = (int) config(sprintf('filesystems.disks.%s.permissions.%s.%s', AbstractSizeVariantNamingStrategy::IMAGE_DISK_NAME, $type, $visibility));
			if ($perm === 0) {
				throw new InvalidConfigOption('Configured file/directory permission is invalid');
			}

			return $perm;
		} catch (ContainerExceptionInterface|BindingResolutionException|NotFoundExceptionInterface $e) {
			throw new InvalidConfigOption('Could not read configuration for file/directory permission', $e);
		}
	}
}
