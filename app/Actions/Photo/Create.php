<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo;

use App\Actions\Photo\Pipes\Duplicate;
use App\Actions\Photo\Pipes\Init;
use App\Actions\Photo\Pipes\PhotoPartner;
use App\Actions\Photo\Pipes\Shared;
use App\Actions\Photo\Pipes\Standalone;
use App\Actions\Photo\Pipes\VideoPartner;
use App\Actions\Statistics\Spaces;
use App\Assets\Features;
use App\Contracts\Exceptions\LycheeException;
use App\Contracts\Models\AbstractAlbum;
use App\DTO\ImportMode;
use App\DTO\ImportParam;
use App\DTO\PhotoCreate\DuplicateDTO;
use App\DTO\PhotoCreate\InitDTO;
use App\DTO\PhotoCreate\PhotoPartnerDTO;
use App\DTO\PhotoCreate\StandaloneDTO;
use App\DTO\PhotoCreate\VideoPartnerDTO;
use App\Exceptions\Internal\LycheeLogicException;
use App\Exceptions\PhotoResyncedException;
use App\Exceptions\PhotoSkippedException;
use App\Exceptions\QuotaExceededException;
use App\Image\Files\NativeLocalFile;
use App\Legacy\Actions\Photo\Create as LegacyPhotoCreate;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pipeline\Pipeline;
use LycheeVerify\Verify;

class Create
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
	 * @throws QuotaExceededException
	 * @throws LycheeException
	 */
	public function add(NativeLocalFile $sourceFile, ?AbstractAlbum $album, ?int $fileLastModifiedTime = null): Photo
	{
		$this->checkQuota($sourceFile);

		if (Features::inactive('create-photo-via-pipes')) {
			$oldCodePath = new LegacyPhotoCreate($this->strategyParameters->importMode, $this->strategyParameters->intendedOwnerId);

			return $oldCodePath->add($sourceFile, $album, $fileLastModifiedTime);
		}

		$initDTO = new InitDTO(
			parameters: $this->strategyParameters,
			sourceFile: $sourceFile,
			album: $album,
			fileLastModifiedTime: $fileLastModifiedTime
		);

		/** @var InitDTO $initDTO */
		$initDTO = app(Pipeline::class)
			->send($initDTO)
			->through([
				Init\AssertSupportedMedia::class,
				Init\FetchLastModifiedTime::class,
				Init\InitParentAlbum::class,
				Init\LoadFileMetadata::class,
				Init\FindDuplicate::class,
				Init\FindLivePartner::class,
			])
			->thenReturn();

		if ($initDTO->duplicate !== null) {
			return $this->handleDuplicate($initDTO);
		}

		if ($initDTO->livePartner === null) {
			return $this->handleStandalone($initDTO);
		}

		// livePartner !== null
		if ($sourceFile->isSupportedVideo()) {
			return $this->handleVideoLivePartner($initDTO);
		}

		if ($sourceFile->isSupportedImage()) {
			return $this->handlePhotoLivePartner($initDTO);
		}

		throw new LycheeLogicException('Pipe system for importing video failed');
	}

	/**
	 * Handle duplicate case.
	 *
	 * @param InitDTO $initDTO initial fetched
	 *
	 * @return Photo Photo duplicated
	 *
	 * @throws PhotoResyncedException
	 * @throws PhotoSkippedException
	 */
	private function handleDuplicate(InitDTO $initDTO): Photo
	{
		$dto = DuplicateDTO::ofInit($initDTO);

		$pipes = [];
		if ($dto->shallResyncMetadata) {
			$pipes[] = Shared\HydrateMetadata::class;
			$pipes[] = Duplicate\SaveIfDirty::class;
		}
		$pipes[] = Duplicate\ThrowSkipDuplicate::class;
		$pipes[] = Duplicate\ReplicateAsPhoto::class;
		$pipes[] = Shared\SetStarred::class;
		$pipes[] = Shared\SetParentAndOwnership::class;
		$pipes[] = Shared\Save::class;
		$pipes[] = Shared\NotifyAlbums::class;

		try {
			return app(Pipeline::class)
				->send($dto)
				->through($pipes)
				->thenReturn()
				->getPhoto();
		} catch (PhotoResyncedException|PhotoSkippedException $e) {
			// duplicate case. Just rethrow.
			throw $e;
		}
	}

	private function handleStandalone(InitDTO $initDTO): Photo
	{
		$dto = StandaloneDTO::ofInit($initDTO);

		$pipes = [
			Standalone\FixTimeStamps::class,
			Standalone\InitNamingStrategy::class,
			Shared\HydrateMetadata::class,
			Shared\SetStarred::class,
			Shared\SetParentAndOwnership::class,
			Standalone\SetOriginalChecksum::class,
			Standalone\FetchSourceImage::class,
			Standalone\ExtractGoogleMotionPictures::class,
			Standalone\PlacePhoto::class,
			Standalone\PlaceGoogleMotionVideo::class,
			Standalone\SetChecksum::class,
			Shared\Save::class,
			Standalone\CreateOriginalSizeVariant::class,
			Standalone\CreateSizeVariants::class,
			Standalone\EncodePlaceholder::class,
			Standalone\ReplaceOriginalWithBackup::class,
			Shared\UploadSizeVariantsToS3::class,
		];

		return $this->executePipeOnDTO($pipes, $dto)->getPhoto();
	}

	private function handleVideoLivePartner(InitDTO $initDTO): Photo
	{
		$dto = VideoPartnerDTO::ofInit($initDTO);

		$pipes = [
			VideoPartner\GetVideoPath::class,
			VideoPartner\PlaceVideo::class,
			VideoPartner\UpdateLivePartner::class,
			Shared\Save::class,
		];

		return $this->executePipeOnDTO($pipes, $dto)->getPhoto();
	}

	/**
	 * Execute the pipes on the DTO.
	 *
	 * @template T of VideoPartnerDTO|StandaloneDTO|PhotoPartnerDTO
	 *
	 * @param array<int,mixed> $pipes
	 * @param T                $dto
	 *
	 * @return T
	 *
	 * @throws LycheeException
	 */
	private function executePipeOnDTO(array $pipes, VideoPartnerDTO|StandaloneDTO|PhotoPartnerDTO $dto): VideoPartnerDTO|StandaloneDTO|PhotoPartnerDTO
	{
		try {
			return app(Pipeline::class)
				->send($dto)
				->through($pipes)
				->thenReturn();
		} catch (LycheeException $e) {
			// If source file could not be put into final destination, remove
			// freshly created photo from DB to avoid having "zombie" entries.
			try {
				/** @disregard */
				$dto->getPhoto()->delete();
			} catch (\Throwable) {
				// Sic! If anything goes wrong here, we still throw the original exception
			}
			throw $e;
		}
	}

	/**
	 * Adds a photo as partner to an existing video.
	 *
	 * Note the asymmetry to {@link handleVideoLivePartner}.
	 *
	 * A photo is always added as if it had no partner, even if the video had
	 * been added first.
	 * Then the already existing video is added to the freshly added photo.
	 * Hence, this strategy works mostly like the stand-alone strategy and also
	 * requires the photo file to be a native, local file in order to be able to
	 * extract EXIF data.
	 */
	private function handlePhotoLivePartner(InitDTO $initDTO): Photo
	{
		// Save old video.
		$oldVideo = $initDTO->livePartner;

		// Import Photo as stand alone.
		$standAloneDto = StandaloneDTO::ofInit($initDTO);
		$standAlonePipes = [
			Standalone\FixTimeStamps::class,
			Standalone\InitNamingStrategy::class,
			Shared\HydrateMetadata::class,
			Shared\SetStarred::class,
			Shared\SetParentAndOwnership::class,
			Standalone\SetOriginalChecksum::class,
			Standalone\FetchSourceImage::class,
			Standalone\ExtractGoogleMotionPictures::class,
			Standalone\PlacePhoto::class,
			Standalone\PlaceGoogleMotionVideo::class,
			Standalone\SetChecksum::class,
			Shared\Save::class,
			Standalone\CreateOriginalSizeVariant::class,
			Standalone\CreateSizeVariants::class,
			Standalone\EncodePlaceholder::class,
			Standalone\ReplaceOriginalWithBackup::class,
			Shared\UploadSizeVariantsToS3::class,
		];
		$standAloneDto = $this->executePipeOnDTO($standAlonePipes, $standAloneDto);

		// Use file from video as input for Video Partner and import
		$videoPartnerDTO = new VideoPartnerDTO(
			videoFile: $oldVideo->size_variants->getOriginal()->getFile(),
			shallDeleteImported: true,
			shallImportViaSymlink: false,
			photo: $standAloneDto->getPhoto()
		);
		$videoPartnerPipes = [
			VideoPartner\GetVideoPath::class,
			VideoPartner\PlaceVideo::class,
			VideoPartner\UpdateLivePartner::class,
			Shared\Save::class,
		];
		$videoPartnerDTO = $this->executePipeOnDTO($videoPartnerPipes, $videoPartnerDTO);

		$finalizeDTO = new PhotoPartnerDTO(
			photo: $videoPartnerDTO->photo,
			oldVideo: $oldVideo
		);

		// Finalize
		$finalize = [
			PhotoPartner\SetOldChecksum::class,
			PhotoPartner\DeleteOldVideoPartner::class,
			Shared\Save::class,
		];

		return $this->executePipeOnDTO($finalize, $finalizeDTO)->getPhoto();
	}

	/**
	 * Check whether the user has enough quota to upload the file.
	 *
	 * @param NativeLocalFile $sourceFile
	 *
	 * @return void
	 *
	 * @throws QuotaExceededException
	 *
	 * @codeCoverageIgnore
	 */
	private function checkQuota(NativeLocalFile $sourceFile): void
	{
		$verify = resolve(Verify::class);

		// if the installation is not validated or
		// if the user is not a supporter, we skip.
		if (!$verify->validate() || !$verify->is_supporter()) {
			return;
		}

		$user = User::find($this->strategyParameters->intendedOwnerId) ?? throw new ModelNotFoundException();

		// User does not have quota
		if ($user->quota_kb === null) {
			return;
		}

		// Admins can upload without quota
		if ($user->may_administrate === true) {
			return;
		}

		$spaces = (new Spaces())->getFullSpacePerUser($user->id);
		$used = $spaces[0]['size'];

		if (($user->quota_kb * 1024) <= $used + $sourceFile->getFilesize()) {
			throw new QuotaExceededException();
		}
	}
}
