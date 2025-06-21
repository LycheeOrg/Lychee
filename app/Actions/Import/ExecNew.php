<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Import;

use App\Actions\Album\Create as AlbumCreate;
use App\Actions\Photo\Create as PhotoCreate;
use App\DTO\BaseImportReport;
use App\DTO\FolderNode;
use App\DTO\ImportEventReport;
use App\DTO\ImportMode;
use App\DTO\ImportProgressReport;
use App\Exceptions\FileOperationException;
use App\Exceptions\Handler;
use App\Exceptions\ImportCancelledException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\InvalidDirectoryException;
use App\Exceptions\ReservedDirectoryException;
use App\Image\Files\NativeLocalFile;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\StringsException;
use function Safe\file;
use function Safe\glob;
use function Safe\ini_get;
use function Safe\ob_flush;
use function Safe\preg_match;
use function Safe\realpath;
use function Safe\set_time_limit;

/**
 * Class for handling improved directory import with tree-based album creation.
 */
class ExecNew
{
	protected ImportMode $import_mode;
	protected PhotoCreate $photo_create;
	protected AlbumCreate $album_create;
	protected bool $enable_cli_formatting = false;
	protected int $mem_limit = 0;
	protected bool $mem_warning_given = false;
	private bool $first_report_given = false;

	/**
	 * @param ImportMode $import_mode           the import mode
	 * @param int        $intended_owner_id     the intended owner ID for the imported photos and albums
	 * @param bool       $enable_cli_formatting determines whether the output shall be formatted for CLI or as JSON
	 * @param int        $mem_limit             the threshold when a memory warning shall be reported; `0` means unlimited
	 */
	public function __construct(
		ImportMode $import_mode,
		int $intended_owner_id,
		bool $enable_cli_formatting,
		int $mem_limit = 0)
	{
		Session::forget('cancel');
		$this->import_mode = $import_mode;
		$this->photo_create = new PhotoCreate($import_mode, $intended_owner_id);
		$this->album_create = new AlbumCreate($intended_owner_id);
		$this->enable_cli_formatting = $enable_cli_formatting;
		$this->mem_limit = $mem_limit;
	}

	/**
	 * Output status update to stdout.
	 *
	 * The output is either sent to a web-client via {@link StreamedResponse}
	 * or to the CLI.
	 *
	 * @param BaseImportReport $report the report
	 *
	 * @return void
	 */
	private function report(BaseImportReport $report): void
	{
		if (!$this->enable_cli_formatting) {
			try {
				echo ($this->first_report_given ? ', ' : '') . $report->toJson();
				$this->first_report_given = true;
				flush();
				ob_flush();
			} catch (JsonEncodingException) {
				// Intentionally left empty
			}
		} else {
			echo $report->toCLIString() . PHP_EOL;
		}

		if ($report instanceof ImportEventReport && $report->getException() !== null) {
			Handler::reportSafely($report->getException());
		}
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
	private static function normalizePath(string $path): string
	{
		try {
			if (str_ends_with($path, '/')) {
				$path = substr($path, 0, -1);
			}
			$real_path = realpath($path);

			if (is_dir($real_path) === false) {
				throw new InvalidDirectoryException('Given path is not a directory (' . $path . ')');
			}

			// Skip folders of Lychee
			if (
				$real_path === Storage::path('thumb') ||
				$real_path === Storage::path('medium') ||
				$real_path === Storage::path('small') ||
				$real_path === Storage::path('import') ||
				$real_path === Storage::path('big') ||
				$real_path === Storage::path('raw')
			) {
				throw new ReservedDirectoryException('The given path is a reserved path of Lychee (' . $path . ')');
			}

			return $path;
		} catch (FilesystemException|StringsException) {
			throw new InvalidDirectoryException('Given path is not a directory (' . $path . ')');
		}
	}

	/**
	 * Reads a list of files to ignore from `.lycheeignore` in the provided directory.
	 *
	 * @param string $path
	 *
	 * @return array<int,string>
	 *
	 * @throws FileOperationException
	 */
	private static function readLocalIgnoreList(string $path): array
	{
		if (is_readable($path . '/.lycheeignore')) {
			try {
				$result = file($path . '/.lycheeignore');
			} catch (\Throwable) {
				throw new FileOperationException('Could not read ' . $path . '/.lycheeignore');
			}

			return $result;
		} else {
			return [];
		}
	}

	/**
	 * @param string   $file
	 * @param string[] $ignore_list
	 *
	 * @return bool
	 */
	private static function checkAgainstIgnoreList(string $file, array $ignore_list): bool
	{
		$ignore_file = false;

		foreach ($ignore_list as $value_ignore) {
			$value_ignore = trim($value_ignore);
			if ($value_ignore === '' || str_starts_with($value_ignore, '#')) {
				continue;
			}
			if (self::check_file_matches_pattern($value_ignore, $file)) {
				$ignore_file = true;
				break;
			}
		}

		return $ignore_file;
	}

	/**
	 * Check if memory usage exceeds limit and report warning if needed.
	 *
	 * @return void
	 */
	private function memWarningCheck(): void
	{
		if ($this->mem_limit !== 0 && !$this->mem_warning_given && memory_get_usage() > $this->mem_limit) {
			$this->report(ImportEventReport::createWarning('memory_limit', null, 'Warning: Memory usage exceeds limit.'));
			$this->mem_warning_given = true;
		}
	}

	/**
	 * @throws ImportCancelledException
	 * @throws FrameworkException
	 */
	private function assertImportNotCancelled(): void
	{
		try {
			if (Session::get('cancel', false) === true) {
				throw new ImportCancelledException();
			}
		} catch (NotFoundExceptionInterface|ContainerExceptionInterface|BindingResolutionException $e) {
			throw new FrameworkException('Session could not be accessed', $e);
		}
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
	 * @throws ImportCancelledException   If the import is cancelled
	 */
	private function buildTree(string $base_path, ?FolderNode $parent_node = null, array $ignore_list = []): FolderNode
	{
		$base_path = self::normalizePath($base_path);
		$local_ignore_list = array_merge($ignore_list, self::readLocalIgnoreList($base_path));

		$folder_name = basename($base_path);
		$node = new FolderNode($folder_name, $base_path, $parent_node);

		$this->populateNodeWithFiles($node, $local_ignore_list);

		return $node;
	}

	/**
	 * Populates a node with files and subdirectories.
	 *
	 * @param FolderNode $node        The node to populate
	 * @param string[]   $ignore_list List of patterns to ignore
	 *
	 * @return void
	 *
	 * @throws ImportCancelledException If the import is cancelled
	 */
	private function populateNodeWithFiles(FolderNode $node, array $ignore_list = []): void
	{
		// Get all files in the current directory
		$files = glob($node->path . '/*');

		foreach ($files as $file) {
			$filename = basename($file);

			// Skip ignored files
			if (self::checkAgainstIgnoreList($filename, $ignore_list)) {
				continue;
			}

			if (is_dir($file)) {
				// Recursively process subdirectories
				$child_node = $this->buildTree($file, $node, $ignore_list);
				$node->children[] = $child_node;
			} elseif (is_file($file)) {
				// Check if this is an image file
				if ($this->isImageFile($file)) {
					$node->images[] = $file;
				}
			}

			$this->memWarningCheck();
			$this->assertImportNotCancelled();
		}
	}

	/**
	 * Check if the file is a supported image file.
	 *
	 * @param string $file File path
	 *
	 * @return bool True if the file is a supported image
	 */
	private function isImageFile(string $file): bool
	{
		// List of supported extensions (should match PhotoCreate class)
		$extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'tif', 'tiff', 'heic', 'heif', 'jxl', 'avif'];
		$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

		return in_array($extension, $extensions, true);
	}

	/**
	 * Create albums and import photos starting from the bottom of the tree.
	 *
	 * @param FolderNode $node         Current node to process
	 * @param Album|null $parent_album Parent album (for nesting)
	 *
	 * @return void
	 *
	 * @throws ImportCancelledException If the import is cancelled
	 */
	private function processNode(FolderNode $node, ?Album $parent_album = null): void
	{
		$this->report(ImportProgressReport::create('Processing folder: ' . $node->name, 0));

		// Process children first (bottom-up approach)
		$this->processChildNodes($node);

		// Check if an album with this title exists under the parent
		$album = $this->findOrCreateAlbum($node->name, $parent_album);
		$node->album = $album;

		// Import all images for this node
		$this->importImagesForNode($node);
	}

	/**
	 * Process all child nodes of a given node.
	 *
	 * @param FolderNode $node Node whose children to process
	 *
	 * @return void
	 *
	 * @throws ImportCancelledException If the import is cancelled
	 */
	private function processChildNodes(FolderNode $node): void
	{
		foreach ($node->children as $child) {
			$this->processNode($child, $node->album);
		}
	}

	/**
	 * Import all images associated with a node into its album.
	 *
	 * @param FolderNode $node Node containing images to import
	 *
	 * @return void
	 *
	 * @throws ImportCancelledException If the import is cancelled
	 */
	private function importImagesForNode(FolderNode $node): void
	{
		foreach ($node->images as $image_path) {
			try {
				$this->importSingleImage($image_path, $node->album);
			} catch (\Throwable $e) {
				$this->report(ImportEventReport::createFromException($e, $image_path));
			}

			$this->memWarningCheck();
			$this->assertImportNotCancelled();
		}
	}

	/**
	 * Import a single image into an album.
	 *
	 * @param string     $image_path Path to the image file
	 * @param Album|null $album      Album to import into
	 *
	 * @return void
	 */
	private function importSingleImage(string $image_path, ?Album $album): void
	{
		// First check if photo already exists in this album by filename
		$filename = basename($image_path);
		if ($this->photoExistsInAlbum($filename, $album)) {
			$this->report(ImportEventReport::createWarning('skip_duplicate', $image_path, 'Skipped existing photo'));

			return;
		}

		$file = new NativeLocalFile($image_path);
		$this->photo_create->add($file, $album);

		$this->report(ImportEventReport::createWarning('imported', $image_path, 'Imported photo'));
	}

	/**
	 * Find an album by title under a parent album or create it if it doesn't exist.
	 *
	 * @param string     $title        Album title
	 * @param Album|null $parent_album Parent album
	 *
	 * @return Album The found or created album
	 */
	private function findOrCreateAlbum(string $title, ?Album $parent_album): Album
	{
		// If we have a parent album, check if the child album already exists
		if ($parent_album !== null) {
			// Find albums with the given title under this parent
			$existing_album = Album::query()
				->where('title', $title)
				->where('parent_id', $parent_album->id)
				->first();
		} else {
			// Check for root-level albums with this title
			$existing_album = Album::query()
				->where('title', $title)
				->whereNull('parent_id')
				->first();

			if ($existing_album !== null) {
				$this->report(ImportEventReport::createWarning('album_exists', $title, 'Using existing album'));
				/** @var Album $album */
				$album = $existing_album;

				return $album;
			}
		}

		if ($existing_album !== null) {
			$this->report(ImportEventReport::createWarning('album_exists', $title, 'Using existing album'));
			/** @var Album $album */
			$album = $existing_album;

			return $album;
		}

		// Album doesn't exist, create it
		$album = $this->album_create->create($title, $parent_album);

		$this->report(ImportEventReport::createWarning('album_created', $title, 'Created new album'));

		return $album;
	}

	/**
	 * Check if a photo with the given filename already exists in the album.
	 *
	 * @param string     $filename Filename to check
	 * @param Album|null $album    Album to check in
	 *
	 * @return bool True if the photo exists
	 */
	private function photoExistsInAlbum(string $filename, ?Album $album): bool
	{
		if ($album === null) {
			return false;
		}

		return $this->findPhotoByFilenameInAlbum($filename, $album->id);
	}

	/**
	 * Find a photo by filename within a specific album.
	 *
	 * @param string $filename Filename to search for
	 * @param string $album_id Album ID to search in
	 *
	 * @return bool True if the photo exists
	 */
	private function findPhotoByFilenameInAlbum(string $filename, string $album_id): bool
	{
		return Photo::query()
			->where('album_id', $album_id)
			->where('original_name', $filename)
			->exists();
	}

	/**
	 * Main method to execute the tree-based import.
	 *
	 * @param string     $path         Base path to import from
	 * @param Album|null $parent_album Optional parent album to import into
	 * @param array      $ignore_list  Optional list of patterns to ignore
	 *
	 * @return void
	 *
	 * @throws \Throwable Any exception that occurs during the import process
	 */
	public function do(
		string $path,
		?Album $parent_album,
		array $ignore_list = [],
	): void {
		try {
			set_time_limit(ini_get('max_execution_time'));
			$this->beginImport($path, $parent_album, $ignore_list);
		} catch (\Throwable $e) {
			$this->report(ImportEventReport::createFromException($e, null));
			throw $e;
		}
	}

	/**
	 * Execute the main import process.
	 *
	 * @param string     $path         Base path to import from
	 * @param Album|null $parent_album Optional parent album to import into
	 * @param array      $ignore_list  Optional list of patterns to ignore
	 *
	 * @return void
	 */
	private function beginImport(string $path, ?Album $parent_album, array $ignore_list = []): void
	{
		$this->report(ImportEventReport::createWarning('import', null, 'Start of Import'));

		// Step 1: Build the tree structure
		$this->report(ImportEventReport::createWarning('build_tree', null, 'Building folder tree...'));
		$root_node = $this->buildTree($path, null, $ignore_list);

		// Step 2: Remove empty nodes recursively
		$this->report(ImportEventReport::createWarning('prune', null, 'Pruning empty folders...'));
		$root_node->pruneEmptyNodes();

		// Step 3 & 4: Create albums and import photos bottom-up
		$this->report(ImportEventReport::createWarning('process', null, 'Creating albums and importing photos...'));
		$this->processNode($root_node, $parent_album);

		$this->report(ImportEventReport::createWarning('complete', null, 'Import complete'));
	}

	/**
	 * @param string $pattern
	 * @param string $filename
	 *
	 * @return bool
	 */
	private static function check_file_matches_pattern(string $pattern, string $filename): bool
	{
		// This function checks if the given filename matches the pattern allowing for
		// star as wildcard (as in *.jpg)
		// Example: '*.jpg' matches all jpgs

		$pattern = preg_replace_callback('/([^*])/', [self::class, 'preg_quote_callback_fct'], $pattern);
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
}
