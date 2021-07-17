<?php

namespace App\Actions\Photo;

use App\Actions\Photo\Extensions\Checks;
use App\Actions\Photo\Extensions\Constants;
use App\Actions\Photo\Extensions\SourceFileInfo;
use App\Actions\Photo\Strategies\AddDuplicateStrategy;
use App\Actions\Photo\Strategies\AddPhotoPartnerStrategy;
use App\Actions\Photo\Strategies\AddStandaloneStrategy;
use App\Actions\Photo\Strategies\AddStrategyParameters;
use App\Actions\Photo\Strategies\AddVideoPartnerStrategy;
use App\Actions\Photo\Strategies\ImportMode;
use App\Exceptions\JsonError;
use App\Factories\AlbumFactory;
use App\Metadata\Extractor;
use App\Models\Photo;

class Create
{
	use Checks;
	use Constants;

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
	 * @param SourceFileInfo  $sourceFileInfo information about source file
	 * @param int|string|null $albumID        the targeted parent album either
	 *                                        null, the id of a real album or
	 *                                        (if it is a string) one of the
	 *                                        array keys in
	 *                                        {@link \App\Factories\SmartFactory::BASE_SMARTS}
	 *
	 * @return Photo|null the newly created or updated photo
	 *
	 * @throws \App\Exceptions\FolderIsNotWritable
	 * @throws \App\Exceptions\JsonError
	 */
	public function add(SourceFileInfo $sourceFileInfo, $albumID = null): Photo
	{
		// Check permissions
		$this->checkPermissions();

		// If the file has been uploaded, the (temporary) source file shall be deleted
		$this->strategyParameters->importMode->setDeleteImported(
			is_uploaded_file($sourceFileInfo->getTmpFullPath())
		);

		// Fill in information about targeted parent album
		$this->initParentId($albumID);

		// Fill in information about source file
		$this->strategyParameters->kind = $this->file_kind($sourceFileInfo);
		$this->strategyParameters->sourceFileInfo = $sourceFileInfo;

		// Fill in meta data extracted from source file
		$this->loadFileMetadata($sourceFileInfo);

		// Look up potential duplicates/partners in order to select the
		// proper strategy
		$duplicate = $this->get_duplicate($this->strategyParameters->info['checksum']);
		$livePartner = $this->findLivePartner(
			$this->strategyParameters->info['live_photo_content_id'],
			$this->strategyParameters->info['type'],
			$this->strategyParameters->album ? $this->strategyParameters->album->id : null
		);

		/*
		 * From here we need to use a strategy depending if we have
		 *
		 *  - a duplicate
		 *  - a "stand-alone" media file (i.e. a photo or video without a partner)
		 *  - a photo which is the partner of an already existing video
		 *  - a video which is the partner of an already existing photo
		 */
		if ($duplicate) {
			$strategy = new AddDuplicateStrategy($this->strategyParameters, $duplicate);
		} else {
			if ($livePartner == null) {
				$strategy = new AddStandaloneStrategy($this->strategyParameters);
			} else {
				if ($this->strategyParameters->kind === 'video') {
					$strategy = new AddVideoPartnerStrategy($this->strategyParameters, $livePartner);
				} else {
					$strategy = new AddPhotoPartnerStrategy($this->strategyParameters, $livePartner);
				}
			}
		}

		return $strategy->do();
	}

	/**
	 * Extracts the meta-data of the source file and initializes
	 * {@link AddStrategyParameters::$info} of {@link Create::$strategyParameters}.
	 *
	 * @param SourceFileInfo $sourceFileInfo information about the source file
	 *
	 * @throws JsonError
	 */
	protected function loadFileMetadata(SourceFileInfo $sourceFileInfo)
	{
		/* @var  Extractor $metadataExtractor */
		$metadataExtractor = resolve(Extractor::class);

		$this->strategyParameters->info = $metadataExtractor->extract($sourceFileInfo->getTmpFullPath(), $this->strategyParameters->kind);
		if ($this->strategyParameters->kind == 'raw') {
			$this->strategyParameters->info['type'] = 'raw';
		}
		if (empty($this->strategyParameters->info['type'])) {
			$this->strategyParameters->info['type'] = $sourceFileInfo->getOriginalMimeType();
		}

		// Use title of file if IPTC title missing
		if ($this->strategyParameters->info['title'] === '') {
			if ($this->strategyParameters->kind == 'raw') {
				$this->strategyParameters->info['title'] = substr(basename($sourceFileInfo->getOriginalFilename()), 0, 98);
			} else {
				$this->strategyParameters->info['title'] = substr(basename($sourceFileInfo->getOriginalFilename(), $sourceFileInfo->getOriginalFileExtension()), 0, 98);
			}
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
	 * @param ?string $contentID the content id to identify a matching partner
	 * @param string  $mimeType  the mime type of the media which a partner is looked for, e.g. the returned {@link Photo} has an "opposed" mime type
	 * @param ?int    $albumID   the album of which the partner must be member of
	 *
	 * @return Photo|null The live partner if found
	 */
	protected function findLivePartner(
		?string $contentID, string $mimeType, ?int $albumID
	): ?Photo {
		$livePartner = null;
		// find a potential partner which has the same content id
		if ($contentID) {
			/** @var Photo|null $livePartner */
			$livePartner = Photo::query()
				->where('live_photo_content_id', '=', $contentID)
				->where('album_id', '=', $albumID)
				->whereNull('live_photo_short_path')->first();
		}
		if ($livePartner != null) {
			// if a potential partner has been found, ensure that it is of a
			// different kind then the uploaded media.
			// Photo+Photo or Video+Video does not work
			// TODO: This condition is probably erroneous, if one of the types equals 'raw'.
			if (in_array($mimeType, $this->validVideoTypes, true) === in_array($livePartner->type, $this->validVideoTypes, true)) {
				$livePartner = null;
			}
		}

		return $livePartner;
	}

	/**
	 * Loads the album for the designated ID and initializes
	 * {@link AddStrategyParameters::$album}, {@link AddStrategyParameters::$public}
	 * and {@link AddStrategyParameters::$star} of
	 * {@link Create::$strategyParameters} accordingly.
	 *
	 * @param int|string|null $albumID the targeted parent album either null,
	 *                                 the id of a real album or (if it is
	 *                                 string) one of the array keys in
	 *                                 {@link \App\Factories\SmartFactory::BASE_SMARTS}
	 *
	 * @throws JsonError
	 * @throws \Illuminate\Contracts\Container\BindingResolutionException
	 */
	protected function initParentId($albumID = null)
	{
		/** @var AlbumFactory */
		$factory = resolve(AlbumFactory::class);
		if (!empty($albumID)) {
			$album = $factory->make($albumID);

			if ($album->is_tag_album()) {
				throw new JsonError('Sorry, cannot upload to Tag Album.');
			}

			if (!$album->is_smart()) {
				$this->strategyParameters->album = $album; // we save it so we don't have to query it again later
			} else {
				$this->strategyParameters->public = ($album->id == 'public');
				$this->strategyParameters->star = ($album->id == 'starred');
			}
		}
	}
}
