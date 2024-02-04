<?php

namespace App\Actions\Photo;

use App\Actions\Photo\Pipes\Duplicate;
use App\Actions\Photo\Pipes\HydrateMetadata;
use App\Actions\Photo\Pipes\Init;
use App\Actions\Photo\Pipes\NotifyAlbums;
use App\Actions\Photo\Pipes\PhotoPartner;
use App\Actions\Photo\Pipes\Save;
use App\Actions\Photo\Pipes\SetParentAndOwnership;
use App\Actions\Photo\Pipes\SetStarred;
use App\Actions\Photo\Pipes\Standalone;
use App\Actions\Photo\Pipes\VideoPartner;
use App\Contracts\Exceptions\LycheeException;
use App\Contracts\Models\AbstractAlbum;
use App\DTO\ImportMode;
use App\DTO\ImportParam;
use App\DTO\PhotoCreateDTO;
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
		if (config('app.photo_pipes') !== true) {
			$oldCodePath = new LegacyPhotoCreate($this->strategyParameters->importMode, $this->strategyParameters->intendedOwnerId);

			return $oldCodePath->add($sourceFile, $album, $fileLastModifiedTime);
		}

		$photoDTO = new PhotoCreateDTO(
			parameters: $this->strategyParameters,
			sourceFile: $sourceFile,
			album: $album,
			fileLastModifiedTime: $fileLastModifiedTime
		);

		$photoDTO = app(Pipeline::class)
			->send($photoDTO)
			->through([
				Init\AssertSupportedMedia::class,
				Init\FetchLastModifiedTime::class,
				Init\InitParentAlbum::class,
				Init\LoadFileMetadata::class,
				Init\FindDuplicate::class,
				Init\FindLivePartner::class,
			])
			->thenReturn();

		$nextPipe = app(Pipeline::class)
			->send($photoDTO);

		if ($photoDTO->duplicate !== null) {
			$nextPipe->pipe($this->getDuplicatePipe($photoDTO));
		} elseif ($photoDTO->livePartner !== null && $sourceFile->isSupportedVideo()) {
			$nextPipe->pipe($this->getVideoPartnerPipe());
		} elseif ($photoDTO->livePartner !== null && $sourceFile->isSupportedImage()) {
			$nextPipe->pipe($this->getPhotoPartnerPipe());
		} else {
			$nextPipe->pipe($this->getStandAlonePipe());
		}

		try {
			return $nextPipe
				->pipe([NotifyAlbums::class])
				->thenReturn()
				->getPhoto();
		} catch (PhotoResyncedException|PhotoSkippedException $e) {
			// duplicate case. Just rethrow.
			throw $e;
		} catch (LycheeException $e) {
			// If source file could not be put into final destination, remove
			// freshly created photo from DB to avoid having "zombie" entries.
			try {
				$photoDTO->getPhoto()->delete();
			} catch (\Throwable) {
				// Sic! If anything goes wrong here, we still throw the original exception
			}
			throw $e;
		}
	}

	private function getDuplicatePipe(PhotoCreateDTO $state): array
	{
		$next = [];

		if ($state->importMode->shallResyncMetadata) {
			array_push($next, ...[
				Duplicate\SetDuplicateAsPhoto::class,
				HydrateMetadata::class,
				Duplicate\SaveIfDirty::class,
			]);
		}
		array_push($next, ...[
			Duplicate\ThrowSkipDuplicate::class,
			Duplicate\ReplicateAsPhoto::class,
			SetStarred::class,
			SetParentAndOwnership::class,
			Save::class,
		]);

		return $next;
	}

	private function getStandAlonePipe(): array
	{
		return [
			Standalone\CreatePhoto::class,
			Standalone\FixTimeStamps::class,
			Standalone\InitNamingStrategy::class,
			HydrateMetadata::class,
			SetStarred::class,
			SetParentAndOwnership::class,
			Standalone\SetOriginalChecksum::class,
			Standalone\FetchSourceImage::class,
			Standalone\ExtractGoogleMotionPictures::class,
			Standalone\PlacePhoto::class,
			Standalone\PlaceGoogleMotionVideo::class,
			Standalone\SetChecksum::class,
			Save::class,
			Standalone\CreateOriginalSizeVariant::class,
			Standalone\CreateSizeVariants::class,
		];
	}

	private function getVideoPartnerPipe(array $steps = [VideoPartner\SetVideoFile::class]): array
	{
		array_push($steps, ...[
			VideoPartner\SetLivePartnerAsPhoto::class,
			VideoPartner\GetVideoPath::class,
			VideoPartner\PlaceVideo::class,
			VideoPartner\UpdateLivePartner::class,
			Save::class,
		]);

		return $steps;
	}

	private function getPhotoPartnerPipe(): array
	{
		$steps = [
			PhotoPartner\SetOldVideoPartner::class,
		];

		array_push($steps, ...$this->getStandAlonePipe());
		array_push($steps, ...[
			PhotoPartner\ResetParameters::class,
			PhotoPartner\ResetFile::class,
			PhotoPartner\ResetLivePartner::class,
		]);
		array_push($steps, ...$this->getVideoPartnerPipe([]));
		array_push($steps, ...[
			PhotoPartner\SetOldChecksum::class,
			PhotoPartner\DeleteOldVideoPartner::class,
			Save::class,
		]);

		return $steps;
	}
}
