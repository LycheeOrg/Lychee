<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Album;

use App\Contracts\Models\AbstractAlbum;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Handler;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\LycheeLogicException;
use App\Image\Files\BaseMediaFile;
use App\Image\Files\FlysystemFile;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\TagAlbum;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use App\SmartAlbums\BaseSmartAlbum;
use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
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
use ZipStream\ZipStream;

abstract class BaseArchive extends Action
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
	 * Resolve which version of the archive to use.
	 *
	 * @return BaseArchive
	 */
	public static function resolve(): self
	{
		if (InstalledVersions::satisfies(new VersionParser(), 'maennchen/zipstream-php', '^3.1')) {
			return new Archive64();
		}
		if (InstalledVersions::satisfies(new VersionParser(), 'maennchen/zipstream-php', '^2.1')) {
			return new Archive32();
		}

		throw new LycheeLogicException('Unsupported version of maennchen/zipstream-php');
	}

	/**
	 * @param Collection<int,AbstractAlbum> $albums
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
			$zip = $this->createZip();

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
			// @codeCoverageIgnoreStart
		} catch (\InvalidArgumentException $e) {
			throw new FrameworkException('Symfony\'s response component', $e);
		}
		// @codeCoverageIgnoreEnd

		return $response;
	}

	/**
	 * @return ZipStream
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	abstract protected function createZip(): ZipStream;

	/**
	 * Create the title of the ZIP archive.
	 *
	 * @param Collection<int,AbstractAlbum> $albums
	 *
	 * @return string
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
		$validTitle = pathinfo($validTitle, PATHINFO_FILENAME);

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
				$this->addFileToZip($zip, $fileName, $file, $photo);
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

	abstract protected function addFileToZip(ZipStream $zip, string $fileName, FlysystemFile|BaseMediaFile $file, Photo|null $photo): void;
}
