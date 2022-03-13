<?php

namespace App\Actions\Import;

use App\Actions\Album\Create as AlbumCreate;
use App\Actions\Photo\Create as PhotoCreate;
use App\Actions\Photo\Extensions\Constants;
use App\Actions\Photo\Extensions\SourceFileInfo;
use App\Actions\Photo\Strategies\ImportMode;
use App\DTO\ImportEventReport;
use App\DTO\ImportProgressReport;
use App\DTO\ImportReport;
use App\Exceptions\FileOperationException;
use App\Exceptions\ImportCancelledException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\InvalidDirectoryException;
use App\Exceptions\MediaFileUnsupportedException;
use App\Exceptions\ReservedDirectoryException;
use App\Facades\Helpers;
use App\Image\NativeLocalFile;
use App\Models\Album;
use App\Models\Configs;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Database\Eloquent\JsonEncodingException;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Exec
{
	use Constants;

	protected ImportMode $importMode;
	protected PhotoCreate $photoCreate;
	protected AlbumCreate $albumCreate;
	protected bool $enableCLIFormatting = false;
	protected int $memLimit = 0;
	protected bool $memWarningGiven = false;
	private array $raw_formats;
	private bool $firstReportGiven = false;
	private ExceptionHandler $exceptionHandler;

	/**
	 * @param ImportMode $importMode          the import mode
	 * @param bool       $enableCLIFormatting determines whether the output shall be formatted for CLI or as JSON
	 * @param int        $memLimit            the threshold when a memory warning shall be reported; `0` means unlimited
	 */
	public function __construct(ImportMode $importMode, bool $enableCLIFormatting, int $memLimit = 0)
	{
		Session::forget('cancel');
		$this->importMode = $importMode;
		$this->photoCreate = new PhotoCreate($importMode);
		$this->albumCreate = new AlbumCreate();
		$this->enableCLIFormatting = $enableCLIFormatting;
		$this->memLimit = $memLimit;
		$this->raw_formats = explode('|', strtolower(Configs::get_value('raw_formats', '')));
		$this->exceptionHandler = resolve(ExceptionHandler::class);
	}

	/**
	 * Output status update to stdout.
	 *
	 * The output is either sent to a web-client via {@link StreamedResponse}
	 * or to the CLI.
	 *
	 * For web-clients this method reports JSON objects.
	 * The outer caller precedes and terminates the whole output by `[` and
	 * `]`, resp., in order to indicate the start and beginning of a JSON
	 * array.
	 * This method also inserts the commas between objects.
	 *
	 * For CLI output we print lines terminated by a newline character.
	 *
	 * If the `ImportReport` carries an exception, the exception is logged
	 * via the standard mechanism of the exception handler.
	 *
	 * @param ImportReport $report the report
	 *
	 * @return void
	 */
	private function report(ImportReport $report): void
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

		if ($report instanceof ImportEventReport && $report->getException()) {
			$this->exceptionHandler->report($report->getException());
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
		if (str_ends_with($path, '/')) {
			$path = substr($path, 0, -1);
		}
		$realPath = realpath($path);

		if (is_dir($realPath) === false) {
			throw new InvalidDirectoryException('Given path is not a directory (' . $path . ')');
		}

		// Skip folders of Lychee
		if (
			$realPath === Storage::path('big') ||
			$realPath === Storage::path('medium') ||
			$realPath === Storage::path('small') ||
			$realPath === Storage::path('thumb')
		) {
			throw new ReservedDirectoryException('The given path is a reserved path of Lychee (' . $path . ')');
		}

		return $path;
	}

	/**
	 * Reads a list of files to ignore from `.lycheeignore` in the provided directory.
	 *
	 * @param string $path
	 *
	 * @return array
	 *
	 * @throws FileOperationException
	 */
	private static function readLocalIgnoreList(string $path): array
	{
		if (is_readable($path . '/.lycheeignore')) {
			$result = file($path . '/.lycheeignore');
			if ($result === false) {
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
			session()->start();
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
		array $ignore_list = []
	) {
		try {
			$path = self::normalizePath($path);

			// Update ignore list
			$ignore_list = array_merge($ignore_list, self::readLocalIgnoreList($path));

			// TODO: Consider to use a modern OO-approach using [`DirectoryIterator`](https://www.php.net/manual/en/class.directoryiterator.php) and [`SplFileInfo`](https://www.php.net/manual/en/class.splfileinfo.php)
			/** @var string[] $files */
			$files = glob($path . '/*');
			if ($files === false) {
				throw new FileOperationException('Could not list directory entries (' . $path . ')');
			}

			$filesTotal = count($files);
			$filesCount = 0;
			$dirs = [];
			$lastStatus = microtime(true);

			$this->report(ImportProgressReport::create($path, 0));
			foreach ($files as $file) {
				$this->assertImportNotCancelled();
				// Reset the execution timeout for every iteration.
				set_time_limit(ini_get('max_execution_time'));
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
					$extension = Helpers::getExtension($file, false);
					$is_raw = in_array(strtolower($extension), $this->raw_formats, true);
					// TODO: Consolidate all mimetype/extension handling in one place; here we have another test whether the source file is supported which is inconsistent with tests elsewhere
					// TODO: Probably the best place is \App\Image\MediaFile.
					// TODO: Consider to make this test a general part of \App\Actions\Photo\Create::add. Then we don't need those tests at multiple places.
					// Note: `exif_imagetype` may also throw an exception
					// (instead of returning `false`), if the file is too small
					// to read enough bytes to determine the file type.
					// So we put `exif_imagetype` last in the condition and
					// exploit lazy evaluation of boolean terms for the case
					// that we import a "short" raw file.
					if ($is_raw || in_array(strtolower($extension), $this->validExtensions, true) || exif_imagetype($file) !== false) {
						$this->photoCreate->add(SourceFileInfo::createByLocalFile(new NativeLocalFile($file)), $parentAlbum);
					} else {
						// TODO: Separately throwing this particular exception should not be necessary, because `photoCreate->add` should do that; see above
						throw new MediaFileUnsupportedException('Unsupported file type');
					}
				} catch (\Throwable $e) {
					$this->report(ImportEventReport::createFromException($e, $file));
				}
			}
			$this->report(ImportProgressReport::create($path, 100));

			// Album creation per directory
			foreach ($dirs as $dir) {
				$this->assertImportNotCancelled();
				$album = $this->importMode->shallSkipDuplicates() ?
					Album::query()
						->select(['albums.*'])
						->join('base_albums', 'base_albums.id', '=', 'albums.id')
						->where('albums.parent_id', '=', $parentAlbum->id)
						->where('base_albums.title', '=', basename($dir))
						->get()
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

		return (bool) preg_match('/^' . $pattern . '$/i', $filename);
	}

	/**
	 * @param array $my_array
	 *
	 * @return string
	 */
	private static function preg_quote_callback_fct(array $my_array): string
	{
		return preg_quote($my_array[1], '/');
	}
}
