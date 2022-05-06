<?php

namespace App\Actions\Photo;

use App\Actions\Photo\Extensions\Checks;
use App\Actions\Photo\Strategies\AddDuplicateStrategy;
use App\Actions\Photo\Strategies\AddPhotoPartnerStrategy;
use App\Actions\Photo\Strategies\AddStandaloneStrategy;
use App\Actions\Photo\Strategies\AddStrategyParameters;
use App\Actions\Photo\Strategies\AddVideoPartnerStrategy;
use App\Actions\Photo\Strategies\ImportMode;
use App\Actions\User\Notify;
use App\Contracts\AbstractAlbum;
use App\Contracts\LycheeException;
use App\Exceptions\ExternalComponentFailedException;
use App\Exceptions\ExternalComponentMissingException;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\InvalidPropertyException;
use App\Exceptions\MediaFileOperationException;
use App\Image\MediaFile;
use App\Image\NativeLocalFile;
use App\Image\StreamStat;
use App\Metadata\Extractor;
use App\Models\Album;
use App\Models\Photo;
use App\SmartAlbums\BaseSmartAlbum;
use App\SmartAlbums\PublicAlbum;
use App\SmartAlbums\StarredAlbum;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Create
{
	use Checks;

	/** @var AddStrategyParameters the strategy parameters prepared and compiled by this class */
	protected AddStrategyParameters $strategyParameters;

	public function __construct(?ImportMode $importMode)
	{
		$this->strategyParameters = new AddStrategyParameters($importMode);
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
	 * @param NativeLocalFile    $sourceFile the source file
	 * @param AbstractAlbum|null $album      the targeted parent album
	 *
	 * @return Photo the newly created or updated photo
	 *
	 * @throws ModelNotFoundException
	 * @throws LycheeException
	 */
	public function add(NativeLocalFile $sourceFile, ?AbstractAlbum $album = null): Photo
	{
		$sourceFile->assertIsSupportedMediaOrAcceptedRaw();

		// Check permissions
		// throws InsufficientFilesystemPermissions
		// TODO: Why do we explicitly perform this check here? We could just let the photo addition fail.
		// There is similar odd test in {@link \App\Actions\Import\FromUrl::__construct()} which uses another "check" trait.
		$this->checkPermissions();

		// Fill in information about targeted parent album
		// throws InvalidPropertyException
		$this->initParentAlbum($album);

		// Fill in metadata extracted from source file
		$this->loadFileMetadata($sourceFile);

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
		if ($duplicate) {
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

		if ($photo->album_id) {
			$notify = new Notify();
			$notify->do($photo);
		}

		return $photo;
	}

	/**
	 * Extracts the meta-data of the source file and initializes
	 * {@link AddStrategyParameters::$exifInfo} of {@link Create::$strategyParameters}.
	 *
	 * @param NativeLocalFile $sourceFile the source file
	 *
	 * @return void
	 *
	 * @throws ExternalComponentMissingException
	 * @throws MediaFileOperationException
	 * @throws ExternalComponentFailedException
	 */
	protected function loadFileMetadata(NativeLocalFile $sourceFile): void
	{
		$this->strategyParameters->exifInfo = Extractor::createFromFile($sourceFile);

		// Use basename of file if IPTC title missing
		if (empty($this->strategyParameters->exifInfo->title)) {
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
		?string $contentID, string $mimeType, ?Album $album
	): ?Photo {
		try {
			$livePartner = null;
			// find a potential partner which has the same content id
			if ($contentID) {
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
					MediaFile::isSupportedImageMimeType($mimeType) && $livePartner->isVideo() ||
					MediaFile::isSupportedVideoMimeType($mimeType) && $livePartner->isPhoto()
				)
			) {
				$livePartner = null;
			}

			return $livePartner;
		} catch (IllegalOrderOfOperationException $e) {
			assert(false, new \AssertionError('IllegalOrderOfOperationException must not be thrown', $e));
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
	protected function initParentAlbum(?AbstractAlbum $album = null)
	{
		if ($album === null) {
			$this->strategyParameters->album = null;
		} elseif ($album instanceof Album) {
			$this->strategyParameters->album = $album;
		} elseif ($album instanceof BaseSmartAlbum) {
			$this->strategyParameters->album = null;
			if ($album instanceof PublicAlbum) {
				$this->strategyParameters->is_public = true;
			} elseif ($album instanceof StarredAlbum) {
				$this->strategyParameters->is_starred = true;
			}
		} else {
			throw new InvalidPropertyException('The given parent album does not support uploading');
		}
	}
}
