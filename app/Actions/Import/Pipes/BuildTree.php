<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Import\Pipes;

use App\Contracts\Import\ImportPipe;
use App\DTO\FolderNode;
use App\DTO\ImportDTO;
use App\DTO\ImportEventReport;
use App\Exceptions\FileOperationException;
use App\Exceptions\InvalidDirectoryException;
use App\Exceptions\ReservedDirectoryException;
use App\Image\Files\BaseMediaFile;
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

	public function __construct()
	{
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

		$state->root_folder = $this->buildTree($state->path);

		return $next($state);
	}

	/**
	 * Builds the tree structure of folders and their images.
	 *
	 * @param string          $base_path   Base path to start building the tree from
	 * @param FolderNode|null $parent_node Parent node (used in recursion)
	 * @param string[]        $ignore_list List of patterns to ignore
	 *
	 * @return FolderNode Root node of the built tree
	 *
	 * @throws InvalidDirectoryException  If the path is not a valid directory
	 * @throws ReservedDirectoryException If the path is a reserved directory
	 */
	private function buildTree(string $base_path, ?FolderNode $parent_node = null, array $ignore_list = []): FolderNode
	{
		$base_path = $this->normalizePath($base_path);
		$local_ignore_list = array_merge($ignore_list, $this->readLocalIgnoreList($base_path));

		$folder_name = basename($base_path);
		$node = new FolderNode($folder_name, $base_path, $parent_node);

		$this->populateNodeWithFiles($node, $local_ignore_list);

		return $node;
	}

	/**
	 * Removes a trailing `/` from the given path and asserts that the path is usable for import.
	 *
	 * @param string $path
	 *
	 * @return string
	 *
	 * @throws ReservedDirectoryException
	 * @throws InvalidDirectoryException
	 */
	private function normalizePath(string $path): string
	{
		try {
			$path = rtrim($path, '/');
			$real_path = realpath($path);

			if (is_dir($real_path) === false) {
				throw new InvalidDirectoryException('Given path is not a directory (' . $path . ')');
			}

			// Skip folders of Lychee
			if (in_array($real_path, $this->folder_skip_list, true)) {
				throw new ReservedDirectoryException('The given path is a reserved path of Lychee (' . $path . ')');
			}

			return $path;
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

			if (is_dir($file)) {
				// Recursively process subdirectories
				$child_node = $this->buildTree($file, $node, $ignore_list);
				$node->children[] = $child_node;
			} elseif (is_file($file)) {
				// Check if this is an image file
				$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
				if (BaseMediaFile::isSupportedOrAcceptedFileExtension('.' . $extension)) {
					$node->images[] = $file;
				}
			}
		}
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

		$pattern = preg_replace_callback('/([^*])/', fn($a) => self::preg_quote_callback_fct($a), $pattern);
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