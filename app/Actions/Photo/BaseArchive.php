<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo;

use App\Actions\Photo\Extensions\ArchiveFileInfo;
use App\Contracts\Exceptions\LycheeException;
use App\Enum\DownloadVariantType;
use App\Enum\SizeVariantType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\InvalidSizeVariantException;
use App\Exceptions\Internal\LycheeLogicException;
use App\Image\Files\BaseMediaFile;
use App\Image\Files\FlysystemFile;
use App\Models\Photo;
use App\Repositories\ConfigManager;
use Composer\InstalledVersions;
use Composer\Semver\VersionParser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Safe\Exceptions\InfoException;
use function Safe\fclose;
use function Safe\fopen;
use function Safe\ini_get;
use function Safe\set_time_limit;
use function Safe\stream_copy_to_stream;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\ZipStream;

abstract class BaseArchive
{
	public const BAD_CHARS = [
		"\x00", "\x01", "\x02", "\x03", "\x04", "\x05", "\x06", "\x07",
		"\x08", "\x09", "\x0a", "\x0b", "\x0c", "\x0d", "\x0e", "\x0f",
		"\x10", "\x11", "\x12", "\x13", "\x14", "\x15", "\x16", "\x17",
		"\x18", "\x19", "\x1a", "\x1b", "\x1c", "\x1d", "\x1e", "\x1f",
		'<', '>', ':', '"', '/', '\\', '|', '?', '*',
	];

	protected int $deflate_level = -1;

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
	 * Returns a response for a downloadable file.
	 *
	 * The file is either a media file (if the array of photo IDs contains
	 * a single element) or a ZIP file (if the array of photo IDs contains
	 * more than one element).
	 *
	 * @param Collection<int,Photo> $photos           the photos which shall be included in the response
	 * @param DownloadVariantType   $download_variant the desired variant of the photo
	 *
	 * @return StreamedResponse
	 *
	 * @throws LycheeException
	 */
	public function do(Collection $photos, DownloadVariantType $download_variant): StreamedResponse
	{
		if ($photos->count() === 1) {
			$response = $this->file($photos->firstOrFail(), $download_variant);
		} else {
			$response = $this->zip($photos, $download_variant);
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
	 * @param Photo               $photo            the photo
	 * @param DownloadVariantType $download_variant the requested size variant
	 *
	 * @return StreamedResponse
	 *
	 * @throws LycheeException
	 */
	protected function file(Photo $photo, DownloadVariantType $download_variant): StreamedResponse
	{
		$archive_file_info = $this->extractFileInfo($photo, $download_variant);

		$response_generator = function () use ($archive_file_info): void {
			$output_stream = fopen('php://output', 'wb');
			stream_copy_to_stream($archive_file_info->file->read(), $output_stream);
			$archive_file_info->file->close();
			fclose($output_stream);
		};

		try {
			$response = new StreamedResponse($response_generator);
			$disposition = HeaderUtils::makeDisposition(
				HeaderUtils::DISPOSITION_ATTACHMENT,
				$archive_file_info->getFilename(),
				mb_check_encoding($archive_file_info->getFilename(), 'ASCII') ? '' : 'Photo' . $archive_file_info->file->getExtension()
			);
			$response->headers->set('Content-Type', $photo->type);
			$response->headers->set('Content-Disposition', $disposition);
			$response->headers->set('Content-Length', strval($archive_file_info->file->getFilesize()));
			// Note: Using insecure hashing algorithm is fine here.
			// The ETag header must only be different for different size variants
			// Pre-image resistance and collision robustness is not required.
			// If a size variant changes, the name of the (physical) file
			// changes, too.
			// The only reason why we don't use the path directly is that
			// we must avoid illegal characters like `/` and md5 returns a
			// hexadecimal string.
			$response->headers->set('ETag', md5(
				$archive_file_info->file->getBasename() .
				$download_variant->value .
				$photo->updated_at->toAtomString() .
				$archive_file_info->file->getFilesize())
			);
			$response->headers->set('Last-Modified', $photo->updated_at->format(\DateTimeInterface::RFC7231));

			return $response;
		} catch (\InvalidArgumentException $e) {
			throw new FrameworkException('Symfony\'s response component', $e);
		}
	}

	/**
	 * @param Collection<int,Photo> $photos
	 * @param DownloadVariantType   $download_variant
	 *
	 * @return StreamedResponse
	 *
	 * @throws FrameworkException
	 * @throws ConfigurationKeyMissingException
	 */
	protected function zip(Collection $photos, DownloadVariantType $download_variant): StreamedResponse
	{
		$config_manager = app(ConfigManager::class);
		$this->deflate_level = $config_manager->getValueAsInt('zip_deflate_level');

		$response_generator = function () use ($download_variant, $photos): void {
			$zip = $this->createZip();

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

			$archive_file_infos = [];
			$unique_filenames = [];
			$ambiguous_filenames = [];

			// Partition the set
			/** @var Photo $photo */
			foreach ($photos as $photo) {
				$archive_file_info = $this->extractFileInfo($photo, $download_variant);
				$archive_file_infos[] = $archive_file_info;
				$filename = $archive_file_info->getFilename();
				if (array_key_exists($filename, $ambiguous_filenames)) {
					// do nothing
				} elseif (array_key_exists($filename, $unique_filenames)) {
					unset($unique_filenames[$filename]);
					$ambiguous_filenames[$filename] = 0;
				} else {
					$unique_filenames[$filename] = 0;
				}
			}

			/** @var ArchiveFileInfo $archive_file_info */
			foreach ($archive_file_infos as $archive_file_info) {
				$true_filename = $archive_file_info->getFilename();
				if (array_key_exists($true_filename, $unique_filenames)) {
					// Easy case: Unique file names are used unaltered
					$filename = $true_filename;
				} else {
					do {
						// Append suffix for multiple copies of same file name
						// but skip results which exist as a unique file name
						$filename = $archive_file_info->getFilename(
							'-' . ++$ambiguous_filenames[$true_filename]
						);
					} while (array_key_exists($filename, $unique_filenames));
				}
				$this->addFileToZip($zip, $filename, $archive_file_info->file, null);
				$archive_file_info->file->close();
				// Reset the execution timeout for every iteration.
				try {
					set_time_limit((int) ini_get('max_execution_time'));
				} catch (InfoException) {
					// Silently do nothing, if `set_time_limit` is denied.
				}
			}

			// finish the zip stream
			$zip->finish();
		};

		try {
			$response = new StreamedResponse($response_generator);
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

	abstract protected function addFileToZip(ZipStream $zip, string $file_name, FlysystemFile|BaseMediaFile $file, Photo|null $photo): void;

	/**
	 * @return ZipStream
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	abstract protected function createZip(): ZipStream;

	/**
	 * Creates a {@link ArchiveFileInfo} for the indicated photo and variant.
	 *
	 * @param Photo               $photo            the photo whose archive information shall be returned
	 * @param DownloadVariantType $download_variant the desired variant of the photo
	 *
	 * @return ArchiveFileInfo the created archive info
	 *
	 * @throws InvalidSizeVariantException
	 */
	protected function extractFileInfo(Photo $photo, DownloadVariantType $download_variant): ArchiveFileInfo
	{
		$valid_filename = str_replace(self::BAD_CHARS, '', $photo->title);
		$base_filename = $valid_filename !== '' ? $valid_filename : 'Untitled';
		$base_filename = pathinfo($base_filename, PATHINFO_FILENAME);

		if ($download_variant === DownloadVariantType::LIVEPHOTOVIDEO) {
			$disk = $photo->size_variants->getSizeVariant(SizeVariantType::ORIGINAL)->storage_disk->value;
			$source_file = new FlysystemFile(Storage::disk($disk), $photo->live_photo_short_path);
			$base_filename_addon = '';
		} else {
			$sv = $photo->size_variants->getSizeVariant($download_variant->getSizeVariantType());
			$base_filename_addon = '';
			if ($sv !== null) {
				$source_file = $sv->getFile();
				// The filename of the original and raw size variants shall get no
				// particular suffix but remain as is.
				// All other size variants (i.e. the generated, smaller ones)
				// get size information as suffix.
				if ($sv->type !== SizeVariantType::ORIGINAL && $sv->type !== SizeVariantType::RAW) {
					$base_filename_addon = '-' . $sv->width . 'x' . $sv->height;
				}
			} else {
				throw new InvalidSizeVariantException('Size variant missing');
			}
		}

		return new ArchiveFileInfo($base_filename, $base_filename_addon, $source_file);
	}
}