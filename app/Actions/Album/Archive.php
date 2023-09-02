<?php

namespace App\Actions\Album;

use App\Contracts\Models\AbstractAlbum;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Handler;
use App\Exceptions\Internal\FrameworkException;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\TagAlbum;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Safe\Exceptions\InfoException;
use function Safe\ini_get;
use function Safe\set_time_limit;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\Exception\FileNotFoundException;
use ZipStream\Exception\FileNotReadableException;
use ZipStream\Option\Archive as ZipArchiveOption;
use ZipStream\Option\File as ZipFileOption;
use ZipStream\Option\Method as ZipMethod;
use ZipStream\ZipStream;

class Archive extends Action
{
	public const BAD_CHARS = [
		"\x00", "\x01", "\x02", "\x03", "\x04", "\x05", "\x06", "\x07",
		"\x08", "\x09", "\x0a", "\x0b", "\x0c", "\x0d", "\x0e", "\x0f",
		"\x10", "\x11", "\x12", "\x13", "\x14", "\x15", "\x16", "\x17",
		"\x18", "\x19", "\x1a", "\x1b", "\x1c", "\x1d", "\x1e", "\x1f",
		'<', '>', ':', '"', '/', '\\', '|', '?', '*',
	];

	protected int $deflateLevel = -1;

	/**
	 * @param Collection<AbstractAlbum> $albums
	 *
	 * @return StreamedResponse
	 *
	 * @throws FrameworkException
	 * @throws ConfigurationKeyMissingException
	 */
	public function do(Collection $albums): StreamedResponse
	{
		// Issue #1950: Setting Model::shouldBeStrict(); in /app/Providers/AppServiceProvider.php breaks recursive album download.
		//
		// From my understanding it is because when we query an album with it's relations (photos & children),
		// the relations of the children are not populated.
		// As a result, when we try to query the picture list of those, it breaks.
		// In that specific case, it is better to simply disable Model::shouldBeStrict() and eat the recursive SQL queries:
		// for this specific case we must allow lazy loading.
		Model::shouldBeStrict(false);

		$this->deflateLevel = Configs::getValueAsInt('zip_deflate_level');

		$responseGenerator = function () use ($albums) {
			$options = new ZipArchiveOption();
			$options->setEnableZip64(Configs::getValueAsBool('zip64'));
			$options->setZeroHeader(true);
			$zip = new ZipStream(null, $options);

			$usedDirNames = [];
			foreach ($albums as $album) {
				$this->compressAlbum($album, $usedDirNames, null, $zip);
			}

			// finish the zip stream
			$zip->finish();
		};

		try {
			$response = new StreamedResponse($responseGenerator);
			// Set file type and destination
			$zipTitle = self::createZipTitle($albums);
			$disposition = HeaderUtils::makeDisposition(
				HeaderUtils::DISPOSITION_ATTACHMENT,
				$zipTitle . '.zip',
				mb_check_encoding($zipTitle, 'ASCII') ? '' : 'Album.zip'
			);
			$response->headers->set('Content-Type', 'application/x-zip');
			$response->headers->set('Content-Disposition', $disposition);

			// Disable caching
			$response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
			$response->headers->set('Pragma', 'no-cache');
			$response->headers->set('Expires', '0');
		} catch (\InvalidArgumentException $e) {
			throw new FrameworkException('Symfony\'s response component', $e);
		}

		return $response;
	}

	/**
	 * Create the title of the ZIP archive.
	 */
	private static function createZipTitle(Collection $albums): string
	{
		return $albums->containsOneItem() ?
			self::createValidTitle($albums->first()->title) :
			'Albums';
	}

	/**
	 * Creates a title which only contains valid characters.
	 *
	 * Removes all invalid characters from the given title.
	 * If the title happens to become the empty string after removing all
	 * illegal characters, the fixed string 'Untitled'  is returned.
	 *
	 * @param string $title the title with possibly invalid characters
	 *
	 * @return string the title without any invalid characters
	 */
	private static function createValidTitle(string $title): string
	{
		$validTitle = str_replace(self::BAD_CHARS, '', $title);

		return $validTitle !== '' ? $validTitle : 'Untitled';
	}

	/**
	 * Returns a unique string.
	 *
	 * Returns the input value `$str` possibly augmented by a counter
	 * suffix `-<n>` such that the returned value is not contained in the
	 * input array `$used`.
	 * The method adds the return value to `$used`.
	 *
	 * @param string        $str  the input string which shall be made unique
	 * @param array<string> $used an input array of previously used strings;
	 *                            the output array will contain the result value
	 *
	 * @return string the unique string
	 */
	private function makeUnique(string $str, array &$used): string
	{
		if (count($used) > 0) {
			$i = 1;
			$tmp = $str;
			while (in_array($tmp, $used, true)) {
				$tmp = $str . '-' . $i;
				$i++;
			}
			$str = $tmp;
		}
		$used[] = $str;

		return $str;
	}

	/**
	 * Compresses an album recursively.
	 *
	 * @param AbstractAlbum $album            the album which shall be added
	 *                                        to the archive
	 * @param array<string> $usedDirNames     the list of already used
	 *                                        directory names on the same level
	 *                                        as `$album`
	 *                                        ("siblings" of `$album`)
	 * @param string|null   $fullNameOfParent the fully qualified path name
	 *                                        of the parent directory
	 * @param ZipStream     $zip              the archive
	 *
	 * @throws FileNotFoundException
	 * @throws FileNotReadableException
	 */
	private function compressAlbum(AbstractAlbum $album, array &$usedDirNames, ?string $fullNameOfParent, ZipStream $zip): void
	{
		$fullNameOfParent = $fullNameOfParent ?? '';

		if (!Gate::check(AlbumPolicy::CAN_DOWNLOAD, [AbstractAlbum::class, $album])) {
			return;
		}

		$fullNameOfDirectory = $this->makeUnique(self::createValidTitle($album->title), $usedDirNames);
		if ($fullNameOfParent !== '') {
			$fullNameOfDirectory = $fullNameOfParent . '/' . $fullNameOfDirectory;
		}

		$usedFileNames = [];
		// TODO: Ensure that the size variant `original` for each photo is eagerly loaded as it is needed below. This must be solved in close coordination with `ArchiveAlbumRequest`.
		$photos = $album->photos;

		/** @var Photo $photo */
		foreach ($photos as $photo) {
			try {
				// For photos in smart or tag albums, skip the ones that are not
				// downloadable based on their actual parent album.  The test for
				// album_id === null shouldn't really be needed as all such photos
				// in smart albums should be owned by the current user...
				if (
					($album instanceof BaseSmartAlbum || $album instanceof TagAlbum) &&
					!Gate::check(PhotoPolicy::CAN_DOWNLOAD, $photo)
				) {
					continue;
				}

				$file = $photo->size_variants->getOriginal()->getFile();

				// Generate name for file inside the ZIP archive
				$fileBaseName = $this->makeUnique(self::createValidTitle($photo->title), $usedFileNames);
				$fileName = $fullNameOfDirectory . '/' . $fileBaseName . $file->getExtension();

				// Reset the execution timeout for every iteration.
				try {
					set_time_limit(intval(ini_get('max_execution_time')));
				} catch (InfoException) {
					// Silently do nothing, if `set_time_limit` is denied.
				}
				$zipFileOption = new ZipFileOption();
				$zipFileOption->setMethod($this->deflateLevel === -1 ? ZipMethod::STORE() : ZipMethod::DEFLATE());
				$zipFileOption->setDeflateLevel($this->deflateLevel);
				$zipFileOption->setComment($photo->title);
				if ($photo->taken_at !== null) {
					$zipFileOption->setTime($photo->taken_at);
				}
				$zip->addFileFromStream($fileName, $file->read(), $zipFileOption);
				$file->close();
			} catch (\Throwable $e) {
				Handler::reportSafely($e);
			}
		}

		// Recursively compress sub-albums
		if ($album instanceof Album) {
			$subDirs = [];
			// TODO: For higher efficiency, ensure that the photos of each child album together with the original size variant are eagerly loaded.
			$subAlbums = $album->children;
			foreach ($subAlbums as $subAlbum) {
				try {
					$this->compressAlbum($subAlbum, $subDirs, $fullNameOfDirectory, $zip);
				} catch (\Throwable $e) {
					Handler::reportSafely($e);
				}
			}
		}
	}
}
