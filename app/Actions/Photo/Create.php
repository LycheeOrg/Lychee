<?php

namespace App\Actions\Photo;

use App\Actions\Photo\Pipes\Duplicate;
use App\Actions\Photo\Pipes\Init;
use App\Actions\Photo\Pipes\Shared;
use App\Actions\Photo\Pipes\Standalone;
use App\Actions\Photo\Pipes\VideoPartner;
use App\Assets\Features;
use App\Contracts\Exceptions\LycheeException;
use App\Contracts\Models\AbstractAlbum;
use App\DTO\ImportMode;
use App\DTO\ImportParam;
use App\DTO\PhotoCreate\DuplicateDTO;
use App\DTO\PhotoCreate\InitDTO;
use App\DTO\PhotoCreate\StandaloneDTO;
use App\DTO\PhotoCreate\VideoPartnerDTO;
use App\Exceptions\PhotoResyncedException;
use App\Exceptions\PhotoSkippedException;
use App\Image\Files\NativeLocalFile;
use App\Legacy\Actions\Photo\Create as LegacyPhotoCreate;
use App\Models\Photo;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pipeline\Pipeline;

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
	 * @throws LycheeException
	 */
	public function add(NativeLocalFile $sourceFile, ?AbstractAlbum $album, ?int $fileLastModifiedTime = null): Photo
	{
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

		$oldCodePath = new LegacyPhotoCreate($this->strategyParameters->importMode, $this->strategyParameters->intendedOwnerId);

		return $oldCodePath->add($sourceFile, $album, $fileLastModifiedTime);
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
		];

		try {
			return app(Pipeline::class)
				->send($dto)
				->through($pipes)
				->thenReturn()
				->getPhoto();
		} catch (LycheeException $e) {
			// If source file could not be put into final destination, remove
			// freshly created photo from DB to avoid having "zombie" entries.
			try {
				$dto->getPhoto()->delete();
			} catch (\Throwable) {
				// Sic! If anything goes wrong here, we still throw the original exception
			}
			throw $e;
		}
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

		try {
			return app(Pipeline::class)
				->send($dto)
				->through($pipes)
				->thenReturn()
				->getPhoto();
		} catch (LycheeException $e) {
			// If source file could not be put into final destination, remove
			// freshly created photo from DB to avoid having "zombie" entries.
			try {
				$dto->getPhoto()->delete();
			} catch (\Throwable) {
				// Sic! If anything goes wrong here, we still throw the original exception
			}
			throw $e;
		}
	}
}
