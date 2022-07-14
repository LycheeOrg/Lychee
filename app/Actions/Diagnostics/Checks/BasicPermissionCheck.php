<?php

namespace App\Actions\Diagnostics\Checks;

use App\Contracts\DiagnosticCheckInterface;
use App\Contracts\SizeVariantNamingStrategy;
use App\Exceptions\Handler;
use App\Exceptions\Internal\InvalidConfigOption;
use App\Facades\Helpers;
use App\Models\SymLink;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Adapter\Local as LocalFlysystem;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use function Safe\sprintf;

class BasicPermissionCheck implements DiagnosticCheckInterface
{
	/**
	 * Image directories must be group-writeable and have the special
	 * `set gid` bit.
	 *
	 * Lychee provides different ways how image files can be added or deleted:
	 * either via the web interface or via console commands such as
	 * `artisan lychee:sync` or `artisan lychee:ghostbuster`.
	 * Usually, the user (process owner) who runs the web server and the
	 * user who runs console commands are different.
	 * This might lead to unfortunate file permission problems such that
	 * the images added via the CLI cannot be deleted via the web UI and
	 * vice versa.
	 *
	 * In order to mitigate the effects the image directories are made
	 * group writable.
	 * Moreover, we set the special `set gid` bit.
	 * For directories, this special bit ensures that newly creates files and
	 * sub-directories get the group of their parent directory and not the
	 * group of the running process.
	 */
	private const VISIBILITY_CATEGORIES = ['private', 'public', 'world'];

	private const FALLBACK_VISIBILITY = 'public';

	private const FALLBACK_DIRECTORY_PERMS = 02775;

	private const FALLBACK_FILE_PERMS = 00664;

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
	 * @param string[] $errors
	 *
	 * @return void
	 */
	public function check(array &$errors): void
	{
		$this->folders($errors);
		$this->userCSS($errors);
	}

	/**
	 * @param string[] $errors
	 *
	 * @return void
	 */
	public function folders(array &$errors): void
	{
		if (!extension_loaded('posix')) {
			return;
		}

		clearstatcache(true);
		$this->numOwnerIssues = 0;
		$this->numPermissionIssues = 0;
		$this->numAccessIssues = 0;
		$groupIDsOrFalse = posix_getgroups();
		if ($groupIDsOrFalse === false) {
			$errors[] = 'Error: Could not determine groups of process';

			return;
		}
		$this->groupIDs = $groupIDsOrFalse;
		$this->groupIDs[] = posix_getegid();
		$this->groupIDs[] = posix_getgid();
		$this->groupIDs = array_unique($this->groupIDs);
		$this->groupNames = implode(', ', array_map(
			function (int $gid): string {
				$groupNameOrFalse = posix_getgrgid($gid);

				return $groupNameOrFalse === false ? '<unknown>' : $groupNameOrFalse['name'];
			},
			$this->groupIDs
		));

		/** @var Filesystem[] $disks */
		$disks = [
			SizeVariantNamingStrategy::getImageDisk(),
			Storage::disk(SymLink::DISK_NAME),
		];

		foreach ($disks as $disk) {
			if ($disk->getDriver()->getAdapter() instanceof LocalFlysystem) {
				$this->checkDirectoryPermissionsRecursively($disk->path(''), $errors);
			}
		}

		if ($this->numOwnerIssues > self::MAX_ISSUE_REPORTS_PER_TYPE) {
			$errors[] = sprintf('Warning: %d more directories with wrong owner', $this->numOwnerIssues - self::MAX_ISSUE_REPORTS_PER_TYPE);
		}
		if ($this->numPermissionIssues > self::MAX_ISSUE_REPORTS_PER_TYPE) {
			$errors[] = sprintf('Warning: %d more directories with wrong permissions', $this->numPermissionIssues - self::MAX_ISSUE_REPORTS_PER_TYPE);
		}
		if ($this->numAccessIssues > self::MAX_ISSUE_REPORTS_PER_TYPE) {
			$errors[] = sprintf('Warning: %d more inaccessible directories', $this->numAccessIssues - self::MAX_ISSUE_REPORTS_PER_TYPE);
		}
	}

	public function userCSS(array &$errors): void
	{
		$p = Storage::disk('dist')->path('user.css');
		if (!Helpers::hasPermissions($p)) {
			$errors[] = "Warning: '" . $p . "' does not exist or has insufficient read/write privileges.";
			$p = Storage::disk('dist')->path('');
			if (!Helpers::hasPermissions($p)) {
				$errors[] = "Warning: '" . $p . "' has insufficient read/write privileges.";
			}
		}
	}

	/**
	 * Check permissions of (local) image directories.
	 *
	 * For efficiency reasons only the directory permissions are checked,
	 * not the permissions of every single file.
	 *
	 * @param string   $path   the path of the directory or file to check
	 * @param string[] $errors the list of errors to append to
	 * @noinspection PhpComposerExtensionStubsInspection
	 */
	private function checkDirectoryPermissionsRecursively(string $path, array &$errors): void
	{
		try {
			if (!is_dir($path)) {
				return;
			}

			$actualPerm = fileperms($path);
			if ($actualPerm === false) {
				$errors[] = sprintf('Warning: Unable to determine permissions for %s' . PHP_EOL, $path);

				return;
			}

			// `fileperms` also returns the higher bits of the inode mode.
			// Hence, we must AND it with 07777 to only get what we are
			// interested in
			$actualPerm &= 07777;
			$owningGroupIdOrFalse = filegroup($path);
			$owningGroupNameOrFalse = $owningGroupIdOrFalse === false ? false : posix_getgrgid($owningGroupIdOrFalse);
			$owningGroupName = $owningGroupNameOrFalse === false ? '<unknown>' : $owningGroupNameOrFalse['name'];
			$expectedPerm = self::getConfiguredDirectoryPerm();

			if (!in_array($owningGroupIdOrFalse, $this->groupIDs, true)) {
				$this->numOwnerIssues++;
				if ($this->numOwnerIssues <= self::MAX_ISSUE_REPORTS_PER_TYPE) {
					$errors[] = sprintf('Warning: %s is owned by group %s, but should be owned by one out of %s', $path, $owningGroupName, $this->groupNames);
				}
			}

			if ($expectedPerm !== $actualPerm) {
				$this->numPermissionIssues++;
				if ($this->numPermissionIssues <= self::MAX_ISSUE_REPORTS_PER_TYPE) {
					$errors[] = sprintf(
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
						(!is_writable($path) && !is_readable($path)) => 'readable nor writable',
						!is_writable($path) => 'writable',
						!is_readable($path) => 'readable',
						default => ''
					};
					$errors[] = sprintf('Error: %s is not %s by %s', $path, $problem, $this->groupNames);
				}
			}

			$dir = new \DirectoryIterator($path);
			foreach ($dir as $dirEntry) {
				if ($dirEntry->isDir() && !$dirEntry->isDot()) {
					$this->checkDirectoryPermissionsRecursively($dirEntry->getPathname(), $errors);
				}
			}
		} catch (\Exception $e) {
			$errors[] = 'Error: ' . $e->getMessage();
			Handler::reportSafely($e);
		}
	}

	/**
	 * @throws InvalidConfigOption
	 */
	public static function getConfiguredDirectoryPerm(): int
	{
		return self::getConfiguredPerm('dir', null, self::FALLBACK_DIRECTORY_PERMS);
	}

	/**
	 * @throws InvalidConfigOption
	 */
	public static function getConfiguredFilePerm(): int
	{
		return self::getConfiguredPerm('file', null, self::FALLBACK_FILE_PERMS);
	}

	/**
	 * @param string      $type       either 'dir' or 'file'
	 * @param string|null $visibility a value out of {@link BasicPermissionCheck::VISIBILITY_CATEGORIES} or `null`
	 *
	 * @return int
	 *
	 * @phpstan-param 'dir'|'file' $type
	 *
	 * @throws InvalidConfigOption
	 */
	private static function getConfiguredPerm(string $type, ?string $visibility, int $fallbackPermission): int
	{
		try {
			$visibility ??= (string) config('filesystems.images.visibility', self::FALLBACK_VISIBILITY);
			if (!in_array($visibility, self::VISIBILITY_CATEGORIES, true)) {
				throw new InvalidConfigOption('Misconfigured directory permissions');
			}

			return (int) config(
				sprintf('filesystems.images.permissions.%s.%s', $type, $visibility),
				$fallbackPermission
			);
		} catch (ContainerExceptionInterface|BindingResolutionException|NotFoundExceptionInterface $e) {
			throw new InvalidConfigOption('Misconfigured directory permissions', $e);
		}
	}
}
