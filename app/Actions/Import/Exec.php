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
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\InfoException;
use Safe\Exceptions\StringsException;
use function Safe\file;
use function Safe\glob;
use function Safe\ini_get;
use function Safe\ob_flush;
use function Safe\preg_match;
use function Safe\realpath;
use function Safe\set_time_limit;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Exec
{
	protected ImportMode $importMode;
	protected PhotoCreate $photoCreate;
	protected AlbumCreate $albumCreate;
	protected bool $enableCLIFormatting = false;
	protected int $memLimit = 0;
	protected bool $memWarningGiven = false;
	private bool $firstReportGiven = false;

	/**
	 * @param ImportMode $importMode          the import mode
	 * @param bool       $enableCLIFormatting determines whether the output shall be formatted for CLI or as JSON
	 * @param int        $memLimit            the threshold when a memory warning shall be reported; `0` means unlimited
	 */
	public function __construct(
		ImportMode $importMode,
		int $intendedOwnerId,
		bool $enableCLIFormatting,
		int $memLimit = 0)
	{
		Session::forget('cancel');
		$this->importMode = $importMode;
		$this->photoCreate = new PhotoCreate($importMode, $intendedOwnerId);
		$this->albumCreate = new AlbumCreate($intendedOwnerId);
		$this->enableCLIFormatting = $enableCLIFormatting;
		$this->memLimit = $memLimit;
	}

	/**
	 * Output status update to stdout.
	 *
	 * The output is either sent to a web-client via {@link StreamedResponse}
	 * or to the CLI.
	 *
	 * For web-clients this method reports JSON objects.
	 * The outer caller precedes and terminates the whole output by `[` and
	 * `]`, resp., in order to indicate the start and end of a JSON
	 * array.
	 * This method also inserts the commas between objects.
	 *
	 * For CLI output we print lines terminated by a newline character.
	 *
	 * If the `ImportReport` carries an exception, the exception is logged
	 * via the standard mechanism of the exception handler.
	 *
	 * @param BaseImportReport $report the report
	 *
	 * @return void
	 */
	private function report(BaseImportReport $report): void
	{
		if (!$this->enableCLIFormatting) {
			try {
				if ($this->firstReportGiven) {
					echo ',';
				}
				echo $report->toJson();
				$this->firstReportGiven = true;
				if (ob_get_level() > 0) {
					ob_flush();
				}
				flush();
			} catch (JsonEncodingException) {
				// do nothing
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
			$realPath = realpath($path);

			if (is_dir($realPath) === false) {
				throw new InvalidDirectoryException('Given path is not a directory (' . $path . ')');
			}

			// Skip folders of Lychee
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
			if (
				$realPath === Storage::path('big') ||
				$realPath === Storage::path('raw') ||
				$realPath === Storage::path('original') ||
				$realPath === Storage::path('medium2x') ||
				$realPath === Storage::path('medium') ||
				$realPath === Storage::path('small2x') ||
				$realPath === Storage::path('small') ||
				$realPath === Storage::path('thumb2x') ||
				$realPath === Storage::path('thumb')
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
			if (self::check_file_matches_pattern(basename($file), $value_ignore)) {
				$ignore_file = true;
				break;
			}
		}

		return $ignore_file;
	}

	private function memWarningCheck(): void
	{
		if ($this->memLimit !== 0 && !$this->memWarningGiven && memory_get_usage() > $this->memLimit) {
			$this->report(ImportEventReport::createWarning('mem_limit', null, 'Approaching memory limit'));
			$this->memWarningGiven = true;
		}
	}

	/**
	 * @throws ImportCancelledException
	 * @throws FrameworkException
	 */
	private function assertImportNotCancelled(): void
	{
		try {
			// re-read session in case cancelling import was requested
			Session::start();
			if (Session::has('cancel')) {
				Session::forget('cancel');
				throw new ImportCancelledException();
			}
		} catch (NotFoundExceptionInterface|ContainerExceptionInterface|BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s session component', $e);
		}
	}

	/**
	 * We process breadth-first: first all the files in a directory,
	 * then the subdirectories.  This way, if the process fails along the
	 * way, it's much easier for the user to figure out what was imported
	 *  and what was not.
	 *
	 * @param string     $path
	 * @param Album|null $parentAlbum
	 * @param string[]   $ignore_list
	 */
	public function do(
		string $path,
		?Album $parentAlbum,
		array $ignore_list = [],
	): void {
		try {
			$path = self::normalizePath($path);

			// Update ignore list
			$ignore_list = array_merge($ignore_list, self::readLocalIgnoreList($path));

			// TODO: Consider to use a modern OO-approach using [`DirectoryIterator`](https://www.php.net/manual/en/class.directoryiterator.php) and [`SplFileInfo`](https://www.php.net/manual/en/class.splfileinfo.php)
			/** @var string[] $files */
			$files = glob(preg_quote($path) . '/*');

			$filesTotal = count($files);
			$filesCount = 0;
			$dirs = [];
			$lastStatus = microtime(true);

			$this->report(ImportProgressReport::create($path, 0));
			foreach ($files as $file) {
				$this->assertImportNotCancelled();
				// Reset the execution timeout for every iteration.
				try {
					set_time_limit((int) ini_get('max_execution_time'));
				} catch (InfoException) {
					// Silently do nothing, if `set_time_limit` is denied.
				}
				// Report if we might be running out of memory.
				$this->memWarningCheck();

				// Generate the status at most each third of a second,
				// except for 0% and 100%, which are always generated.
				// Generating more frequently would create unnecessary many status
				// reports; generating less frequently might lead to Firefox
				// complaining.
				// Firefox considers any response with a delay of >=500ms as
				// "unresponsive".
				// Taking additional delays on the network layer into account,
				// 1/3 second should be fine.
				$time = microtime(true);
				if ($time - $lastStatus >= 0.3) {
					$this->report(ImportProgressReport::create($path, $filesCount / $filesTotal * 100));
					$lastStatus = $time;
				}

				// Let's check if we should ignore the file
				if (self::checkAgainstIgnoreList($file, $ignore_list)) {
					$filesTotal--;
					continue;
				}

				if (is_dir($file)) {
					$dirs[] = $file;
					$filesTotal--;
					continue;
				}

				$filesCount++;

				try {
					$this->photoCreate->add(new NativeLocalFile($file), $parentAlbum);
				} catch (\Throwable $e) {
					$this->report(ImportEventReport::createFromException($e, $file));
				}
			}
			$this->report(ImportProgressReport::create($path, 100));

			// Album creation per directory
			foreach ($dirs as $dir) {
				$this->assertImportNotCancelled();
				/** @var Album|null */
				$album = $this->importMode->shallSkipDuplicates ?
					Album::query()
						->select(['albums.*'])
						->join('base_albums', 'base_albums.id', '=', 'albums.id')
						->where('albums.parent_id', '=', $parentAlbum?->id)
						->where('base_albums.title', '=', basename($dir))
						->first() :
					null;
				if ($album === null) {
					$album = $this->albumCreate->create(basename($dir), $parentAlbum);
				}
				$this->do($dir . '/', $album, $ignore_list);
			}
		} catch (\Throwable $e) {
			$this->report(ImportEventReport::createFromException($e, $path));
		}
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
