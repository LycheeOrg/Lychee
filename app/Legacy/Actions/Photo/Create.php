<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\Actions\Photo;

use App\Actions\User\Notify;
use App\Contracts\Exceptions\LycheeException;
use App\Contracts\Models\AbstractAlbum;
use App\DTO\ImportMode;
use App\DTO\ImportParam;
use App\Exceptions\ExternalComponentFailedException;
use App\Exceptions\ExternalComponentMissingException;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Exceptions\Internal\LycheeAssertionError;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\InvalidPropertyException;
use App\Exceptions\MediaFileOperationException;
use App\Image\Files\BaseMediaFile;
use App\Image\Files\NativeLocalFile;
use App\Image\StreamStat;
use App\Legacy\Actions\Photo\Strategies\AddDuplicateStrategy;
use App\Legacy\Actions\Photo\Strategies\AddPhotoPartnerStrategy;
use App\Legacy\Actions\Photo\Strategies\AddStandaloneStrategy;
use App\Legacy\Actions\Photo\Strategies\AddVideoPartnerStrategy;
use App\Metadata\Extractor;
use App\Models\Album;
use App\Models\Photo;
use App\SmartAlbums\BaseSmartAlbum;
use App\SmartAlbums\StarredAlbum;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use function Safe\filemtime;

final class Create
{
	/** @var ImportParam the strategy parameters prepared and compiled by this class */
	protected ImportParam $strategyParameters;

	public function __construct(?ImportMode $importMode, int $intendedOwnerId)
	{
		$this->strategyParameters = new ImportParam($importMode, $intendedOwnerId);
	}

	/**
	 * Adds/imports the designated source file to Lychee.
	 *
	 * Depending on the type and origin of the source file as well as
	 * depending on operational settings, this method applies different
	 * strategies.
	 * This method may create a new database entry or update an existing
	 * database entry.
	 *
	 * @param NativeLocalFile    $sourceFile           the source file
	 * @param int|null           $fileLastModifiedTime the timestamp to use if there's no creation date in Exif
	 * @param AbstractAlbum|null $album                the targeted parent album
	 *
	 * @return Photo the newly created or updated photo
	 *
	 * @throws ModelNotFoundException
	 * @throws LycheeException
	 */
	public function add(NativeLocalFile $sourceFile, ?AbstractAlbum $album, ?int $fileLastModifiedTime = null): Photo
	{
		$fileLastModifiedTime ??= filemtime($sourceFile->getRealPath());

		$sourceFile->assertIsSupportedMediaOrAcceptedRaw();

		// Fill in information about targeted parent album
		// throws InvalidPropertyException
		$this->initParentAlbum($album);

		// Fill in metadata extracted from source file
		$this->loadFileMetadata($sourceFile, $fileLastModifiedTime);

		// Look up potential duplicates/partners in order to select the
		// proper strategy
		$duplicate = $this->get_duplicate(StreamStat::createFromLocalFile($sourceFile)->checksum);
		$livePartner = $this->findLivePartner(
			$this->strategyParameters->exifInfo->livePhotoContentID,
			$this->strategyParameters->exifInfo->type,
			$this->strategyParameters->album
		);

		/*
		 * From here we need to use a strategy depending on whether we have
		 *
		 *  - a duplicate
		 *  - a "stand-alone" media file (i.e. a photo or video without a partner)
		 *  - a photo which is the partner of an already existing video
		 *  - a video which is the partner of an already existing photo
		 */
		if ($duplicate !== null) {
			$strategy = new AddDuplicateStrategy($this->strategyParameters, $duplicate);
		} else {
			if ($livePartner === null) {
				$strategy = new AddStandaloneStrategy($this->strategyParameters, $sourceFile);
			} else {
				if ($sourceFile->isSupportedVideo()) {
					$strategy = new AddVideoPartnerStrategy($this->strategyParameters, $sourceFile, $livePartner);
				} elseif ($sourceFile->isSupportedImage()) {
					$strategy = new AddPhotoPartnerStrategy($this->strategyParameters, $sourceFile, $livePartner);
				} else {
					// Accepted, but unsupported raw files are added as stand-alone files
					$strategy = new AddStandaloneStrategy($this->strategyParameters, $sourceFile);
				}
			}
		}

		$photo = $strategy->do();

		if ($photo->album_id !== null) {
			$notify = new Notify();
			$notify->do($photo);
		}

		return $photo;
	}

	/**
	 * Extracts the meta-data of the source file and initializes
	 * {@link ImportParam::$exifInfo} of {@link Create::$strategyParameters}.
	 *
	 * @param NativeLocalFile $sourceFile           the source file
	 * @param int             $fileLastModifiedTime the timestamp to use if there's no creation date in Exif
	 *
	 * @return void
	 *
	 * @throws ExternalComponentMissingException
	 * @throws MediaFileOperationException
	 * @throws ExternalComponentFailedException
	 */
	protected function loadFileMetadata(NativeLocalFile $sourceFile, int $fileLastModifiedTime): void
	{
		$this->strategyParameters->exifInfo = Extractor::createFromFile($sourceFile, $fileLastModifiedTime);

		// Use basename of file if IPTC title missing
		if (
			$this->strategyParameters->exifInfo->title === null ||
			$this->strategyParameters->exifInfo->title === ''
		) {
			$this->strategyParameters->exifInfo->title = substr($sourceFile->getOriginalBasename(), 0, 98);
		}
	}

	/**
	 * Finds a "lonely" live partner if it exists.
	 *
	 * A lonely live partner is a media entry which
	 *  - has the same content ID
	 *  - is in the same album
	 *  - which has an "opposed" mime type (i.e. only mixed (video,photo) or
	 *    (photo,video) pairs can be partners
	 *  - which has no live partner yet
	 *
	 * @param string|null $contentID the content id to identify a matching partner
	 * @param string      $mimeType  the mime type of the media which a partner is looked for, e.g. the returned {@link Photo} has an "opposed" mime type
	 * @param Album|null  $album     the album of which the partner must be member of
	 *
	 * @return Photo|null The live partner if found
	 *
	 * @throws QueryBuilderException
	 */
	protected function findLivePartner(
		?string $contentID,
		string $mimeType,
		?Album $album,
	): ?Photo {
		try {
			$livePartner = null;
			// find a potential partner which has the same content id
			if ($contentID !== null) {
				/** @var Photo|null $livePartner */
				$livePartner = Photo::query()
					->where('live_photo_content_id', '=', $contentID)
					->where('album_id', '=', $album?->id)
					->whereNull('live_photo_short_path')->first();
			}
			// if a potential partner has been found, ensure that it is of a
			// different kind then the uploaded media.
			if (
				$livePartner !== null && !(
					BaseMediaFile::isSupportedImageMimeType($mimeType) && $livePartner->isVideo() ||
					BaseMediaFile::isSupportedVideoMimeType($mimeType) && $livePartner->isPhoto()
				)
			) {
				$livePartner = null;
			}

			return $livePartner;
		} catch (IllegalOrderOfOperationException $e) {
			throw LycheeAssertionError::createFromUnexpectedException($e);
		}
	}

	/**
	 * Sets the (regular) parent album of {@link Create::$strategyParameters}
	 * according to the provided parent album.
	 *
	 * If the provided parent album equals `null` or is already a (regular)
	 * album, then the strategy is set to that album.
	 * If the provided parent album is one of the built-in smart albums,
	 * then the (regular) parent album of the strategy is set to `null` (aka
	 * the root album) and the other properties of the strategy are tweaked
	 * such that the photo will be shown by the smart album.
	 *
	 * @param AbstractAlbum|null $album the targeted parent album
	 *
	 * @throws InvalidPropertyException
	 */
	protected function initParentAlbum(?AbstractAlbum $album = null): void
	{
		if ($album === null) {
			$this->strategyParameters->album = null;
		} elseif ($album instanceof Album) {
			$this->strategyParameters->album = $album;
		} elseif ($album instanceof BaseSmartAlbum) {
			$this->strategyParameters->album = null;
			if ($album instanceof StarredAlbum) {
				$this->strategyParameters->is_starred = true;
			}
		} else {
			throw new InvalidPropertyException('The given parent album does not support uploading');
		}
	}

	/**
	 * Check if a picture has a duplicate
	 * We compare the checksum to the other Photos or LivePhotos.
	 *
	 * @param string $checksum
	 *
	 * @return ?Photo
	 */
	public function get_duplicate(string $checksum): ?Photo
	{
		/** @var Photo|null $photo */
		$photo = Photo::query()
			->where('checksum', '=', $checksum)
			->orWhere('original_checksum', '=', $checksum)
			->orWhere('live_photo_checksum', '=', $checksum)
			->first();

		return $photo;
	}
}
