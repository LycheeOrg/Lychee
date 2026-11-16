<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Import\Pipes;

use App\Actions\Import\ImportPathGuard;
use App\Contracts\Import\ImportPipe;
use App\DTO\FolderNode;
use App\DTO\ImportDTO;
use App\DTO\ImportEventReport;
use App\Exceptions\FileOperationException;
use App\Exceptions\InvalidDirectoryException;
use App\Exceptions\ReservedDirectoryException;
use App\Services\Image\FileExtensionService;
use Illuminate\Support\Facades\Storage;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\StringsException;
use function Safe\file;
use function Safe\glob;
use function Safe\preg_match;
use function Safe\preg_replace_callback;
use function Safe\realpath;

class BuildTree implements ImportPipe
{
	use HasReporterTrait;

	private array $folder_skip_list = [];

	/** Real import root links must stay in, `null` when unrestricted (FR-115-11). */
	private ?string $import_root = null;

	/**
	 * Real paths of the directories already in the tree (loops, several links to one folder).
	 *
	 * @var array<string,true>
	 */
	private array $visited = [];

	public function __construct(
		protected readonly FileExtensionService $file_extension_service,
	) {
		// Preload the folders skip list to avoid repeated calls to Storage::path()
		// This list contains the paths of folders that are reserved by Lychee and should not be used for imports.
		//
		// Currently we must check for each directory which might be used
		// by Lychee below `uploads/` individually, because the folder
		// `uploads/import` is a potential source for imports and also
		// placed below `uploads`.
		// This is a design error and needs to be changed, at last when
		// the media is stored remotely on a network storage such as
		// AWS S3.
		// A much better folder structure would be
		//
		// ```
		//  |
		//  +-- staging           // new directory which temporarily stores media which is not yet, but going to be added to Lychee
		//  |     +-- imports     // replaces the current `uploads/import`
		//  |     +-- uploads     // temporary storage location for images which have been uploaded via an HTTP POST request
		//  |     +-- downloads   // temporary storage location for images which have been downloaded from a remote URL
		//  +-- vault             // replaces the current `uploads/` and could be outsourced to a remote network storage
		//        +-- original
		//        +-- medium2x
		//        +-- medium
		//        +-- small2x
		//        +-- small
		//        +-- thumb2x
		//        +-- thumb
		// ```
		//
		// This way we could simply check if the path is anything below `vault`
		$this->folder_skip_list = [
			Storage::path('big'),
			Storage::path('raw'),
			Storage::path('original'),
			Storage::path('medium2x'),
			Storage::path('medium'),
			Storage::path('small2x'),
			Storage::path('small'),
			Storage::path('thumb2x'),
			Storage::path('thumb'),
		];
	}

	public function handle(ImportDTO $state, \Closure $next): ImportDTO
	{
		$this->report(ImportEventReport::createNotice('build_tree', $state->path, 'Building folder tree...'));

		$this->import_root = $state->import_root;
		$this->visited = [];
		$state->root_folder = $this->buildTree($state->path);

		return $next($state);
	}

	/**
	 * Builds the tree structure of folders and their images.
	 *
	 * @param string          $base_path   Base path to start building the tree from
	 * @param FolderNode|null $parent_node Parent node (used in recursion)
	 * @param string[]        $ignore_list List of patterns to ignore
	 * @param string|null     $real_path   Real path of $base_path when already resolved
	 *
	 * @return FolderNode Root node of the built tree
	 *
	 * @throws InvalidDirectoryException  If the path is not a valid directory
	 * @throws ReservedDirectoryException If the path is a reserved directory
	 */
	private function buildTree(string $base_path, ?FolderNode $parent_node = null, array $ignore_list = [], ?string $real_path = null): FolderNode
	{
		[$base_path, $real_path] = $this->normalizePath($base_path, $real_path);
		$this->visited[$real_path] = true;
		$local_ignore_list = array_merge($ignore_list, $this->readLocalIgnoreList($base_path));

		$folder_name = basename($base_path);
		$node = new FolderNode($folder_name, $base_path, $parent_node);

		$this->populateNodeWithFiles($node, $local_ignore_list);

		return $node;
	}

	/**
	 * Removes a trailing `/` from the given path and asserts that the path is usable for import.
	 *
	 * @param string      $path
	 * @param string|null $real_path real path of $path when already resolved
	 *
	 * @return array{0:string,1:string} the path without trailing `/` and its real path
	 *
	 * @throws ReservedDirectoryException
	 * @throws InvalidDirectoryException
	 */
	private function normalizePath(string $path, ?string $real_path = null): array
	{
		try {
			$path = rtrim($path, '/');
			$real_path ??= realpath($path);

			if (is_dir($real_path) === false) {
				throw new InvalidDirectoryException('Given path is not a directory (' . $path . ')');
			}

			// Skip folders of Lychee
			if (in_array($real_path, $this->folder_skip_list, true)) {
				throw new ReservedDirectoryException('The given path is a reserved path of Lychee (' . $path . ')');
			}

			return [$path, $real_path];
			// @codeCoverageIgnoreStart
		} catch (FilesystemException|StringsException) {
			throw new InvalidDirectoryException('Given path is not a directory (' . $path . ')');
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Populates a node with files and subdirectories.
	 *
	 * @param FolderNode $node        The node to populate
	 * @param string[]   $ignore_list List of patterns to ignore
	 *
	 * @return void
	 */
	private function populateNodeWithFiles(FolderNode $node, array $ignore_list = []): void
	{
		// TODO: Consider to use a modern OO-approach using [`DirectoryIterator`](https://www.php.net/manual/en/class.directoryiterator.php) and [`SplFileInfo`](https://www.php.net/manual/en/class.splfileinfo.php)
		// ? The OO-approach is slightly slower.
		// Get all files in the current directory
		/** @var string[] $files */
		$files = glob($node->path . '/*');

		foreach ($files as $file) {
			$filename = basename($file);

			// Skip ignored files
			if ($this->checkAgainstIgnoreList($filename, $ignore_list)) {
				continue;
			}

			$real_path = $this->followedRealPath($file);
			if ($real_path === null) {
				continue;
			}

			if (is_dir($real_path)) {
				// Recursively process subdirectories
				$child_node = $this->buildTree($file, $node, $ignore_list, $real_path);
				$node->children[] = $child_node;
			} elseif (is_file($real_path)) {
				// Check if this is an image file
				$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
				if ($this->file_extension_service->isSupportedOrAcceptedFileExtension('.' . $extension)) {
					$node->images[] = $file;
				}
			}
		}
	}

	/**
	 * Real path of an entry that is part of the tree, `null` when it is skipped
	 * (FR-115-11, NFR-115-07: one `realpath()` per entry).
	 *
	 * Entries whose real path leaves the import root (links) are skipped,
	 * as are directories already in the tree (loops, several links to one folder)
	 * and dangling links.
	 */
	private function followedRealPath(string $file): ?string
	{
		$real_path = ImportPathGuard::resolve($file);
		if ($real_path === null) {
			return null;
		}

		if ($this->import_root !== null && !ImportPathGuard::isWithin($real_path, $this->import_root)) {
			$this->report(ImportEventReport::createWarning('symlink_outside', $file, 'Skipped: the link leaves the import root'));

			return null;
		}

		if (isset($this->visited[$real_path])) {
			$this->report(ImportEventReport::createNotice('already_visited', $file, 'Skipped: folder already imported'));

			return null;
		}

		return $real_path;
	}

	/**
	 * @param string   $file
	 * @param string[] $ignore_list
	 *
	 * @return bool
	 */
	private function checkAgainstIgnoreList(string $file, array $ignore_list): bool
	{
		$ignore_file = false;

		foreach ($ignore_list as $value_ignore) {
			$value_ignore = trim($value_ignore);
			if ($this->check_file_matches_pattern($value_ignore, $file)) {
				$ignore_file = true;
				break;
			}
		}

		return $ignore_file;
	}

	/**
	 * @param string $pattern
	 * @param string $filename
	 *
	 * @return bool
	 */
	private function check_file_matches_pattern(string $pattern, string $filename): bool
	{
		// This function checks if the given filename matches the pattern allowing for
		// star as wildcard (as in *.jpg)
		// Example: '*.jpg' matches all jpgs

		$pattern = preg_replace_callback('/([^*])/', fn ($a) => self::preg_quote_callback_fct($a), $pattern);
		$pattern = str_replace('*', '.*', $pattern);

		return preg_match('/^' . $pattern . '$/i', $filename) === 1;
	}

	/**
	 * @param array<int,string> $my_array
	 *
	 * @return string
	 */
	private static function preg_quote_callback_fct(array $my_array): string
	{
		return preg_quote($my_array[1], '/');
	}

	/**
	 * Reads a list of files to ignore from `.lycheeignore` in the provided directory.
	 * We ignore lines that are empty or start with `#` (comments).
	 *
	 * @param string $path
	 *
	 * @return array<int,string>
	 *
	 * @throws FileOperationException
	 */
	private function readLocalIgnoreList(string $path): array
	{
		if (is_readable($path . '/.lycheeignore')) {
			try {
				$result = file($path . '/.lycheeignore');
			} catch (\Throwable) {
				throw new FileOperationException('Could not read ' . $path . '/.lycheeignore');
			}

			$result = array_map('trim', $result);
			$result = array_filter($result, fn ($v) => $v === '' || !str_starts_with($v, '#'));

			return array_values($result);
		}

		return [];
	}
}