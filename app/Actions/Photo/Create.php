<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo;

use App\Actions\Photo\Pipes\Duplicate;
use App\Actions\Photo\Pipes\Init;
use App\Actions\Photo\Pipes\PhotoPartner;
use App\Actions\Photo\Pipes\Shared;
use App\Actions\Photo\Pipes\Standalone;
use App\Actions\Photo\Pipes\VideoPartner;
use App\Actions\Statistics\Spaces;
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
use App\Models\Photo;
use App\Models\User;
use App\Services\Image\FileExtensionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pipeline\Pipeline;
use LycheeVerify\Contract\VerifyInterface;

class Create
{
	/** @var ImportParam the strategy parameters prepared and compiled by this class */
	protected ImportParam $strategy_parameters;

	public function __construct(
		?ImportMode $import_mode,
		int $intended_owner_id,
	) {
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
	 * @throws QuotaExceededException
	 * @throws LycheeException
	 */
	public function add(NativeLocalFile $source_file, ?AbstractAlbum $album, ?int $file_last_modified_time = null): Photo
	{
		$this->checkQuota($source_file);

		/** @var InitDTO $init_dto */
		$init_dto = new InitDTO(
			parameters: $this->strategy_parameters,
			source_file: $source_file,
			album: $album,
			file_last_modified_time: $file_last_modified_time
		);

		$pre_pipes = [
			Init\ConvertUnsupportedMedia::class,
			Init\AssertSupportedMedia::class,
			Init\FetchLastModifiedTime::class,
			Init\MayLoadFileMetadata::class,
			Init\FindDuplicate::class,
		];

		$init_dto = app(Pipeline::class)
			->send($init_dto)
			->through($pre_pipes)
			->thenReturn();

		if ($init_dto->duplicate !== null) {
			return $this->handleDuplicate($init_dto);
		}

		$post_pipes = [
			Init\InitParentAlbum::class,
			Init\LoadFileMetadata::class,
			Init\FindLivePartner::class,
		];

		$init_dto = app(Pipeline::class)
			->send($init_dto)
			->through($post_pipes)
			->thenReturn();

		if ($init_dto->live_partner === null) {
			return $this->handleStandalone($init_dto);
		}

		// livePartner !== null
		$file_extension_service = app(FileExtensionService::class);
		if ($file_extension_service->isSupportedVideo($source_file->getMimeType(), $source_file->getOriginalExtension())) {
			return $this->handleVideoLivePartner($init_dto);
		}

		if ($file_extension_service->isSupportedImage($source_file->getPath(), $source_file->getMimeType(), $source_file->getOriginalExtension())) {
			return $this->handlePhotoLivePartner($init_dto);
		}

		throw new LycheeLogicException('Pipe system for importing video failed');
	}

	/**
	 * Handle duplicate case.
	 *
	 * @param InitDTO $init_dto initial fetched
	 *
	 * @return Photo Photo duplicated
	 *
	 * @throws PhotoResyncedException
	 * @throws PhotoSkippedException
	 */
	private function handleDuplicate(InitDTO $init_dto): Photo
	{
		$dto = DuplicateDTO::ofInit($init_dto);

		$pipes = [];
		if ($dto->shall_resync_metadata) {
			$pipes[] = Shared\HydrateMetadata::class;
			$pipes[] = Duplicate\SaveIfDirty::class;
		}
		$pipes[] = Duplicate\ThrowSkipDuplicate::class;
		$pipes[] = Shared\SetHighlighted::class;
		$pipes[] = Shared\Save::class;
		$pipes[] = Shared\SetParent::class;
		$pipes[] = Shared\SaveStatistics::class;
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

	private function handleStandalone(InitDTO $init_dto): Photo
	{
		$dto = StandaloneDTO::ofInit($init_dto);

		$pipes = [
			Standalone\FixTimeStamps::class,
			Standalone\InitNamingStrategy::class,
			Shared\HydrateMetadata::class,
			Shared\SetHighlighted::class,
			Shared\SetOwnership::class,
			Standalone\SetOriginalChecksum::class,
			Standalone\FetchSourceImage::class,
			Standalone\ExtractGoogleMotionPictures::class,
			Standalone\PlacePhoto::class,
			Standalone\PlaceGoogleMotionVideo::class,
			Standalone\SetChecksum::class,
			Standalone\AutoRenamer::class,
			Shared\Save::class,
			Shared\SetParent::class,
			Shared\SaveStatistics::class,
			Standalone\CreateOriginalSizeVariant::class,
			Standalone\CreateSizeVariants::class,
			Standalone\ApplyWatermark::class,
			Standalone\EncodePlaceholder::class,
			Standalone\ReplaceOriginalWithBackup::class,
			Shared\UploadSizeVariantsToS3::class,
			Shared\ExtractColourPalette::class,
		];

		return $this->executePipeOnDTO($pipes, $dto)->getPhoto();
	}

	private function handleVideoLivePartner(InitDTO $init_dto): Photo
	{
		$dto = VideoPartnerDTO::ofInit($init_dto);

		$pipes = [
			VideoPartner\GetVideoPath::class,
			VideoPartner\PlaceVideo::class,
			VideoPartner\UpdateLivePartner::class,
			Shared\Save::class,
			Shared\SaveStatistics::class,
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
	private function handlePhotoLivePartner(InitDTO $init_dto): Photo
	{
		// Save old video.
		$old_video = $init_dto->live_partner;

		// Import Photo as stand alone.
		$stand_alone_dto = StandaloneDTO::ofInit($init_dto);
		$stand_alone_pipes = [
			Standalone\FixTimeStamps::class,
			Standalone\InitNamingStrategy::class,
			Shared\HydrateMetadata::class,
			Shared\SetHighlighted::class,
			Shared\SetOwnership::class,
			Standalone\SetOriginalChecksum::class,
			Standalone\FetchSourceImage::class,
			Standalone\ExtractGoogleMotionPictures::class,
			Standalone\PlacePhoto::class,
			Standalone\PlaceGoogleMotionVideo::class,
			Standalone\SetChecksum::class,
			Standalone\AutoRenamer::class,
			Shared\Save::class,
			Shared\SetParent::class,
			Standalone\CreateOriginalSizeVariant::class,
			Standalone\CreateSizeVariants::class,
			Standalone\ApplyWatermark::class,
			Standalone\EncodePlaceholder::class,
			Standalone\ReplaceOriginalWithBackup::class,
			Shared\UploadSizeVariantsToS3::class,
			Shared\ExtractColourPalette::class,
		];
		$stand_alone_dto = $this->executePipeOnDTO($stand_alone_pipes, $stand_alone_dto);

		// Use file from video as input for Video Partner and import
		$video_partner_dto = new VideoPartnerDTO(
			video_file: $old_video->size_variants->getOriginal()->getFile(),
			shall_delete_imported: true,
			shall_import_via_symlink: false,
			photo: $stand_alone_dto->getPhoto()
		);
		$video_partner_pipes = [
			VideoPartner\GetVideoPath::class,
			VideoPartner\PlaceVideo::class,
			VideoPartner\UpdateLivePartner::class,
			Shared\Save::class,
		];
		$video_partner_dto = $this->executePipeOnDTO($video_partner_pipes, $video_partner_dto);

		$finalize_dto = new PhotoPartnerDTO(
			photo: $video_partner_dto->photo,
			old_video: $old_video
		);

		// Finalize
		$finalize = [
			PhotoPartner\SetOldChecksum::class,
			PhotoPartner\DeleteOldVideoPartner::class,
			Shared\Save::class,
			Shared\SaveStatistics::class,
		];

		return $this->executePipeOnDTO($finalize, $finalize_dto)->getPhoto();
	}

	/**
	 * Check whether the user has enough quota to upload the file.
	 *
	 * @param NativeLocalFile $source_file
	 *
	 * @return void
	 *
	 * @throws QuotaExceededException
	 *
	 * @codeCoverageIgnore
	 */
	private function checkQuota(NativeLocalFile $source_file): void
	{
		// if the installation is not validated or
		// if the user is not a supporter, we skip.
		$verify = app(VerifyInterface::class);
		if (!$verify->is_supporter()) {
			return;
		}

		$user = User::find($this->strategy_parameters->intended_owner_id) ?? throw new ModelNotFoundException();

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

		if (($user->quota_kb * 1024) <= $used + $source_file->getFilesize()) {
			throw new QuotaExceededException();
		}
	}
}