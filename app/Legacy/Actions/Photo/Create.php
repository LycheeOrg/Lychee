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
	protected ImportParam $strategy_parameters;

	public function __construct(?ImportMode $import_mode, int $intended_owner_id)
	{
		$this->strategy_parameters = new ImportParam($import_mode, $intended_owner_id);
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
	 * @param NativeLocalFile    $source_file             the source file
	 * @param int|null           $file_last_modified_time the timestamp to use if there's no creation date in Exif
	 * @param AbstractAlbum|null $album                   the targeted parent album
	 *
	 * @return Photo the newly created or updated photo
	 *
	 * @throws ModelNotFoundException
	 * @throws LycheeException
	 */
	public function add(NativeLocalFile $source_file, ?AbstractAlbum $album, ?int $file_last_modified_time = null): Photo
	{
		$file_last_modified_time ??= filemtime($source_file->getRealPath());

		$source_file->assertIsSupportedMediaOrAcceptedRaw();

		// Fill in information about targeted parent album
		// throws InvalidPropertyException
		$this->initParentAlbum($album);

		// Fill in metadata extracted from source file
		$this->loadFileMetadata($source_file, $file_last_modified_time);

		// Look up potential duplicates/partners in order to select the
		// proper strategy
		$duplicate = $this->get_duplicate(StreamStat::createFromLocalFile($source_file)->checksum);
		$live_partner = $this->findLivePartner(
			$this->strategy_parameters->exif_info->live_photo_content_id,
			$this->strategy_parameters->exif_info->type,
			$this->strategy_parameters->album
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
			$strategy = new AddDuplicateStrategy($this->strategy_parameters, $duplicate);
		} else {
			if ($live_partner === null) {
				$strategy = new AddStandaloneStrategy($this->strategy_parameters, $source_file);
			} else {
				if ($source_file->isSupportedVideo()) {
					$strategy = new AddVideoPartnerStrategy($this->strategy_parameters, $source_file, $live_partner);
				} elseif ($source_file->isSupportedImage()) {
					$strategy = new AddPhotoPartnerStrategy($this->strategy_parameters, $source_file, $live_partner);
				} else {
					// Accepted, but unsupported raw files are added as stand-alone files
					$strategy = new AddStandaloneStrategy($this->strategy_parameters, $source_file);
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
	 * {@link ImportParam::$exif_info} of {@link Create::$strategy_parameters}.
	 *
	 * @param NativeLocalFile $source_file             the source file
	 * @param int             $file_last_modified_time the timestamp to use if there's no creation date in Exif
	 *
	 * @return void
	 *
	 * @throws ExternalComponentMissingException
	 * @throws MediaFileOperationException
	 * @throws ExternalComponentFailedException
	 */
	protected function loadFileMetadata(NativeLocalFile $source_file, int $file_last_modified_time): void
	{
		$this->strategy_parameters->exif_info = Extractor::createFromFile($source_file, $file_last_modified_time);

		// Use basename of file if IPTC title missing
		if (
			$this->strategy_parameters->exif_info->title === null ||
			$this->strategy_parameters->exif_info->title === ''
		) {
			$this->strategy_parameters->exif_info->title = substr($source_file->getOriginalBasename(), 0, 98);
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
	 * @param string|null $content_id the content id to identify a matching partner
	 * @param string      $mime_type  the mime type of the media which a partner is looked for, e.g. the returned {@link Photo} has an "opposed" mime type
	 * @param Album|null  $album      the album of which the partner must be member of
	 *
	 * @return Photo|null The live partner if found
	 *
	 * @throws QueryBuilderException
	 */
	protected function findLivePartner(
		?string $content_id,
		string $mime_type,
		?Album $album,
	): ?Photo {
		try {
			$live_partner = null;
			// find a potential partner which has the same content id
			if ($content_id !== null) {
				/** @var Photo|null $live_partner */
				$live_partner = Photo::query()
					->where('live_photo_content_id', '=', $content_id)
					->where('album_id', '=', $album?->id)
					->whereNull('live_photo_short_path')->first();
			}
			// if a potential partner has been found, ensure that it is of a
			// different kind then the uploaded media.
			if (
				$live_partner !== null && !(
					BaseMediaFile::isSupportedImageMimeType($mime_type) && $live_partner->isVideo() ||
					BaseMediaFile::isSupportedVideoMimeType($mime_type) && $live_partner->isPhoto()
				)
			) {
				$live_partner = null;
			}

			return $live_partner;
		} catch (IllegalOrderOfOperationException $e) {
			throw LycheeAssertionError::createFromUnexpectedException($e);
		}
	}

	/**
	 * Sets the (regular) parent album of {@link Create::$strategy_parameters}
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
			$this->strategy_parameters->album = null;
		} elseif ($album instanceof Album) {
			$this->strategy_parameters->album = $album;
		} elseif ($album instanceof BaseSmartAlbum) {
			$this->strategy_parameters->album = null;
			if ($album instanceof StarredAlbum) {
				$this->strategy_parameters->is_starred = true;
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