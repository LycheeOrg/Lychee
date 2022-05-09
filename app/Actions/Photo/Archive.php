<?php

namespace App\Actions\Photo;

use App\Actions\Photo\Extensions\ArchiveFileInfo;
use App\Contracts\LycheeException;
use App\Contracts\SizeVariantNamingStrategy;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\InvalidSizeVariantException;
use App\Image\FlysystemFile;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\SizeVariant;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Collection;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\ZipStream;

class Archive
{
	public const LIVEPHOTOVIDEO = 'LIVEPHOTOVIDEO';
	public const FULL = 'FULL';
	public const MEDIUM2X = 'MEDIUM2X';
	public const MEDIUM = 'MEDIUM';
	public const SMALL2X = 'SMALL2X';
	public const SMALL = 'SMALL';
	public const THUMB2X = 'THUMB2X';
	public const THUMB = 'THUMB';

	public const VARIANTS = [
		self::LIVEPHOTOVIDEO,
		self::FULL,
		self::MEDIUM2X,
		self::MEDIUM,
		self::SMALL2X,
		self::SMALL,
		self::THUMB2X,
		self::THUMB,
	];

	public const VARIANT2VARIANT = [
		self::FULL => SizeVariant::ORIGINAL,
		self::MEDIUM2X => SizeVariant::MEDIUM2X,
		self::MEDIUM => SizeVariant::MEDIUM,
		self::SMALL2X => SizeVariant::SMALL2X,
		self::SMALL => SizeVariant::SMALL,
		self::THUMB2X => SizeVariant::THUMB2X,
		self::THUMB => SizeVariant::THUMB,
	];

	private array $badChars;

	public function __construct()
	{
		// Illicit chars
		$this->badChars = array_merge(array_map('chr', range(0, 31)), ['<', '>', ':', '"', '/', '\\', '|', '?', '*']);
	}

	/**
	 * Returns a response for a downloadable file.
	 *
	 * The file is either a media file (if the array of photo IDs contains
	 * a single element) or a ZIP file (if the array of photo IDs contains
	 * more than one element).
	 *
	 * @param Collection<Photo> $photos  the photos which shall be included
	 *                                   in the response
	 * @param string            $variant the desired variant of the photo;
	 *                                   valid values are
	 *                                   {@link Archive::LIVEPHOTOVIDEO},
	 *                                   {@link Archive::FULL},
	 *                                   {@link Archive::MEDIUM2X},
	 *                                   {@link Archive::MEDIUM},
	 *                                   {@link Archive::SMALL2X},
	 *                                   {@link Archive::SMALL},
	 *                                   {@link Archive::THUMB2X},
	 *                                   {@link Archive::THUMB}
	 *
	 * @return StreamedResponse
	 *
	 * @throws LycheeException
	 */
	public function do(Collection $photos, string $variant): StreamedResponse
	{
		if ($photos->count() === 1) {
			$response = $this->file($photos->first(), $variant);
		} else {
			$response = $this->zip($photos, $variant);
		}

		return $response;
	}

	/**
	 * Streams a single size variant to the client.
	 *
	 * Note: This method will become quite inefficient, when the media files
	 * are not hosted on the same machine as Lychee, but on a remote file
	 * hosting service like AWS S3.
	 * In this case, the file would be streamed from the hoster to Lychee
	 * first and then streamed from Lychee to the client.
	 * It would be much more efficient, if the client would directly fetch
	 * the file from the hoster.
	 * Practically, we could use `->getUrl` of the size variant in combination
	 * with `Symfony\Component\HttpFoundation\RedirectResponse`.
	 * However, the client would not get a "nice" file name, but the
	 * random file name of the size variant.
	 *
	 * @param Photo  $photo   the photo
	 * @param string $variant the requested size variant
	 *
	 * @return StreamedResponse
	 *
	 * @throws LycheeException
	 */
	protected function file(Photo $photo, string $variant): StreamedResponse
	{
		$archiveFileInfo = $this->extractFileInfo($photo, $variant);

		$responseGenerator = function () use ($archiveFileInfo) {
			$outputStream = fopen('php://output', 'wb');
			stream_copy_to_stream($archiveFileInfo->getFile()->read(), $outputStream);
			$archiveFileInfo->getFile()->close();
			fclose($outputStream);
		};

		try {
			$response = new StreamedResponse($responseGenerator);
			$disposition = HeaderUtils::makeDisposition(
				HeaderUtils::DISPOSITION_ATTACHMENT,
				$archiveFileInfo->getFilename()
			);
			$response->headers->set('Content-Type', $photo->type);
			$response->headers->set('Content-Disposition', $disposition);
			$response->headers->set('Content-Length', $archiveFileInfo->getFile()->getFilesize());
			// Note: Using insecure hashing algorithm is fine here.
			// The ETag header must only be different for different size variants
			// Pre-image resistance and collision robustness is not required.
			// If a size variant changes, the name of the (physical) file
			// changes, too.
			// The only reason why we don't use the path directly is that
			// we must avoid illegal characters like `/` and md5 returns a
			// hexadecimal string.
			$response->headers->set('ETag', md5($archiveFileInfo->getFile()->getAbsolutePath()));
			$response->headers->set('Last-Modified', $photo->updated_at->format(DateTimeInterface::RFC7231));

			return $response;
		} catch (\InvalidArgumentException $e) {
			throw new FrameworkException('Symfony\'s response component', $e);
		}
	}

	/**
	 * @param Collection $photos
	 * @param string     $variant
	 *
	 * @return StreamedResponse
	 *
	 * @throws FrameworkException
	 */
	protected function zip(Collection $photos, string $variant): StreamedResponse
	{
		$responseGenerator = function () use ($variant, $photos) {
			$options = new \ZipStream\Option\Archive();
			$options->setEnableZip64(Configs::get_value('zip64', '1') === '1');
			$zip = new ZipStream(null, $options);

			// We first need to scan the whole array of files to avoid
			// problems with duplicate file names.
			// If a file name occurs multiple times, the files are named
			// filename-1, filename-2, filename-3 and so on.
			// Unfortunately, the naive approach which uses a simple online
			// algorithm that only applies a singly pass (without look-ahead)
			// and maintains a counter for every file name will fail, if the
			// list of file names already contains another files which uses
			// the same naming pattern accidentally.
			// Assume that the album itself contains the images
			// `my-file.jpg`, `my-file-2.jpg`, `my-file.jpg`.
			// The naive approach would first store `my-file.jpg` and
			// `my-file-2.jpg` (both unaltered).
			// Both counters for `my-file.jpg` and `my-file-2.jpg` equal one
			// because those file names are actually treated as independent
			// file names.
			// When the naive approach comes across the last file
			// `my-file.jpg`, the counter for `my-file.jpg` is incremented
			// and the file is stored as ``my-file-2.jpg`.
			// However, this accidentally overwrite the original
			// `my-file-2.jpg`.
			// Long story short, if we append a counter as a suffix to a
			// filename, we must take care that the result is not also used as
			// a base file name.
			// Further note, that this problem does not occur if both file
			// names occurred multiple times.
			// E.g., if we had
			//   - `my-file.jpg`,
			//   - `my-file-2.jpg`,
			//   - `my-file.jpg` and
			//   - `my-file-2.jpg` again,
			// then the result would be
			//   - `my-file-1.jpg`,
			//   - `my-file-2-1.jpg`,
			//   - `my-file-2.jpg` and
			//   - `my-file-2-2.jpg`.
			// Note that the problematic case can only occur due to a clash
			// of file names between file names which occur multiple times
			// (and thus are appended by a suffix) and a file name that only
			// occurs a single time.
			//
			// Here, we take the following approach:
			//
			// We scan the list of photos once and partition the set of file
			// names into a set of unique file names and a set of ambitious
			// file names.
			// In the second run, all photos with unique file names are
			// stored under their unaltered file name.
			// For photo with an ambiguous file name a counter for that file
			// name is tracked and incremented.
			// If the resulting file name accidentally equals one of the
			// unique file names, then the counter is incremented until the
			// next "free" file name is found.

			$archiveFileInfos = [];
			$uniqueFilenames = [];
			$ambiguousFilenames = [];

			// Partition the set
			/** @var Photo $photo */
			foreach ($photos as $photo) {
				$archiveFileInfo = $this->extractFileInfo($photo, $variant);
				$archiveFileInfos[] = $archiveFileInfo;
				$filename = $archiveFileInfo->getFilename();
				if (array_key_exists($filename, $ambiguousFilenames)) {
					continue;
				} elseif (array_key_exists($filename, $uniqueFilenames)) {
					unset($uniqueFilenames[$filename]);
					$ambiguousFilenames[$filename] = 0;
				} else {
					$uniqueFilenames[$filename] = 0;
				}
			}

			/** @var ArchiveFileInfo $archiveFileInfo */
			foreach ($archiveFileInfos as $archiveFileInfo) {
				$trueFilename = $archiveFileInfo->getFilename();
				if (array_key_exists($trueFilename, $uniqueFilenames)) {
					// Easy case: Unique file names are used unaltered
					$filename = $trueFilename;
				} else {
					do {
						// Append suffix for multiple copies of same file name
						// but skip results which exist as a unique file name
						$filename = $archiveFileInfo->getFilename(
							'-' . ++$ambiguousFilenames[$trueFilename]
						);
					} while (array_key_exists($filename, $uniqueFilenames));
				}
				$zip->addFileFromStream($filename, $archiveFileInfo->getFile()->read());
				$archiveFileInfo->getFile()->close();
				// Reset the execution timeout for every iteration.
				set_time_limit(ini_get('max_execution_time'));
			}

			// finish the zip stream
			$zip->finish();
		};

		try {
			$response = new StreamedResponse($responseGenerator);
			$disposition = HeaderUtils::makeDisposition(
				HeaderUtils::DISPOSITION_ATTACHMENT,
				'Photos.zip'
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
	 * Creates a {@link ArchiveFileInfo} for the indicated photo and variant.
	 *
	 * @param Photo  $photo   the photo whose archive information
	 *                        shall be returned
	 * @param string $variant the desired variant of the photo; valid values
	 *                        are
	 *                        {@link Archive::LIVEPHOTOVIDEO},
	 *                        {@link Archive::FULL},
	 *                        {@link Archive::MEDIUM2X},
	 *                        {@link Archive::MEDIUM},
	 *                        {@link Archive::SMALL2X},
	 *                        {@link Archive::SMALL},
	 *                        {@link Archive::THUMB2X},
	 *                        {@link Archive::THUMB}
	 *
	 * @return ArchiveFileInfo the created archive info
	 *
	 * @throws InvalidSizeVariantException
	 */
	protected function extractFileInfo(Photo $photo, string $variant): ArchiveFileInfo
	{
		$baseFilename = str_replace($this->badChars, '', $photo->title) ?: 'Untitled';

		if ($variant === self::LIVEPHOTOVIDEO) {
			$sourceFile = new FlysystemFile(SizeVariantNamingStrategy::getImageDisk(), $photo->live_photo_short_path);
			$baseFilenameAddon = '';
		} elseif (array_key_exists($variant, self::VARIANT2VARIANT)) {
			$sv = $photo->size_variants->getSizeVariant(self::VARIANT2VARIANT[$variant]);
			$baseFilenameAddon = '';
			if ($sv) {
				$sourceFile = $sv->getFile();
				// The filename of the original size variant shall get no
				// particular suffix but remain as is.
				// All other size variants (i.e. the generated, smaller ones)
				// get size information as suffix.
				if ($sv->type !== SizeVariant::ORIGINAL) {
					$baseFilenameAddon = '-' . $sv->width . 'x' . $sv->height;
				}
			} else {
				throw new InvalidSizeVariantException('Size variant missing');
			}
		} else {
			throw new InvalidSizeVariantException('Invalid type of size variant ' . $variant);
		}

		return new ArchiveFileInfo($baseFilename, $baseFilenameAddon, $sourceFile);
	}
}
