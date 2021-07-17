<?php

namespace App\Actions\Photo;

use App\Actions\Photo\Extensions\ArchiveFileInfo;
use App\Actions\Photo\Extensions\Constants;
use App\Facades\AccessControl;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipStream\ZipStream;

class Archive
{
	use Constants;

	const LIVEPHOTOVIDEO = 'LIVEPHOTOVIDEO';
	const FULL = 'FULL';
	const MEDIUM2X = 'MEDIUM2X';
	const MEDIUM = 'MEDIUM';
	const SMALL2X = 'SMALL2X';
	const SMALL = 'SMALL';
	const THUMB2X = 'THUMB2X';
	const THUMB = 'THUMB';

	const VARIANTS = [
		self::LIVEPHOTOVIDEO,
		self::FULL,
		self::MEDIUM2X,
		self::MEDIUM,
		self::SMALL2X,
		self::SMALL,
		self::THUMB2X,
		self::THUMB,
	];

	const VARIANT2VARIANT = [
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
	 * @param int[]  $photoIDs the IDs of the photos which shall be included
	 *                         in the response
	 * @param string $variant  the desired variant of the photo; valid values
	 *                         are
	 *                         {@link Archive::LIVEPHOTOVIDEO},
	 *                         {@link Archive::FULL},
	 *                         {@link Archive::MEDIUM2X},
	 *                         {@link Archive::MEDIUM},
	 *                         {@link Archive::SMALL2X},
	 *                         {@link Archive::SMALL},
	 *                         {@link Archive::THUMB2X},
	 *                         {@link Archive::THUMB}
	 *
	 * @return Response
	 */
	public function do(array $photoIDs, string $variant): Response
	{
		if (count($photoIDs) === 1) {
			$response = $this->file($photoIDs[0], $variant);
		} else {
			$response = $this->zip($photoIDs, $variant);
		}

		return $response;
	}

	protected function file($photoID, $variant): BinaryFileResponse
	{
		$archiveFileInfo = $this->extractFileInfo($photoID, $variant);
		if ($archiveFileInfo === null) {
			abort(404);
		}
		$response = new BinaryFileResponse($archiveFileInfo->getFullPath());

		return $response->setContentDisposition(
			ResponseHeaderBag::DISPOSITION_ATTACHMENT,
			$archiveFileInfo->getFilename()
		);
	}

	protected function zip(array $photoIDs, string $variant): StreamedResponse
	{
		$response = new StreamedResponse(function () use ($variant, $photoIDs) {
			$options = new \ZipStream\Option\Archive();
			$options->setEnableZip64(Configs::get_value('zip64', '1') === '1');
			$zip = new ZipStream(null, $options);

			$fileCounter = [];
			foreach ($photoIDs as $photoID) {
				$archiveFileInfo = $this->extractFileInfo($photoID, $variant);
				if ($archiveFileInfo == null) {
					abort(404);
				}

				// Set title for photo
				$filename = $archiveFileInfo->getFilename();
				// Check for duplicates
				if (array_key_exists($filename, $fileCounter)) {
					$cnt = $fileCounter[$filename];
					$cnt++;
					$fileCounter[$filename] = $cnt;
					$filename = $archiveFileInfo->getFilename('-' . $cnt);
				} else {
					$fileCounter[$filename] = 1;
				}

				// Reset the execution timeout for every iteration.
				set_time_limit(ini_get('max_execution_time'));

				$zip->addFileFromPath($filename, $archiveFileInfo->getFullPath());
			} // foreach ($photoIDs)

			// finish the zip stream
			$zip->finish();
		});

		// Set file type and destination
		$response->headers->set('Content-Type', 'application/x-zip');
		$disposition = HeaderUtils::makeDisposition(HeaderUtils::DISPOSITION_ATTACHMENT, 'Photos.zip');
		$response->headers->set('Content-Disposition', $disposition);

		return $response;
	}

	/**
	 * Creates a {@link ArchiveFileInfo} for the indicated photo and variant.
	 *
	 * @param int    $photoID the id of the photo whose archive information
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
	 * @return ArchiveFileInfo|null the created archive info
	 */
	public function extractFileInfo(int $photoID, string $variant): ?ArchiveFileInfo
	{
		/** @var Photo $photo */
		$photo = Photo::with('album')->findOrFail($photoID);

		if (!AccessControl::is_current_user($photo->owner_id)) {
			if ($photo->album_id !== null) {
				if (!$photo->album->is_downloadable()) {
					return null;
				}
			} elseif (Configs::get_value('downloadable', '0') === '0') {
				return null;
			}
		}

		$baseFilename = str_replace($this->badChars, '', $photo->title) ?: 'Untitled';

		if ($variant === self::LIVEPHOTOVIDEO) {
			$shortPath = $photo->live_photo_short_path;
			$baseFilenameAddon = '';
		} elseif (array_key_exists($variant, self::VARIANT2VARIANT)) {
			$sv = $photo->size_variants->getSizeVariant(self::VARIANT2VARIANT[$variant]);
			$shortPath = '';
			$baseFilenameAddon = '';
			if ($sv) {
				$shortPath = $sv->short_path;
				// The filename of the original size variant shall get no
				// particular suffix but remain as is.
				// All other size variants (i.e. the generated, smaller ones)
				// get a size information as suffix.
				if ($sv->size_variant !== SizeVariant::ORIGINAL) {
					$baseFilenameAddon = '-' . $sv->width . 'x' . $sv->height;
				}
			}
		} else {
			$msg = 'Invalid variant ' . $variant;
			Logs::error(__METHOD__, __LINE__, $msg);
			throw new \InvalidArgumentException($msg);
		}

		// Check if file actually exists
		if (empty($shortPath) || !Storage::exists($shortPath)) {
			Logs::error(__METHOD__, __LINE__, 'File is missing: ' . $shortPath . ' (' . $baseFilename . ')');

			return null;
		}

		return new ArchiveFileInfo($baseFilename, $baseFilenameAddon, Storage::path($shortPath));
	}
}
