<?php

namespace App\Actions\Photo\Strategies;

use App\Exceptions\ConfigurationException;
use App\Exceptions\Internal\LycheeAssertionError;
use App\Exceptions\Internal\LycheeLogicException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\ModelDBException;
use App\Facades\AccessControl;
use App\Image\FlysystemFile;
use App\Image\MediaFile;
use App\Image\NativeLocalFile;
use App\Models\Photo;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Adapter\Local;
use function Safe\symlink;

abstract class AddBaseStrategy
{
	public const IMAGE_DISK_NAME = 'images';

	protected AddStrategyParameters $parameters;
	protected Photo $photo;

	protected function __construct(AddStrategyParameters $parameters, Photo $photo)
	{
		$this->parameters = $parameters;
		$this->photo = $photo;
	}

	/**
	 * @return Photo
	 *
	 * @throws ModelDBException
	 * @throws MediaFileOperationException
	 */
	abstract public function do(): Photo;

	/**
	 * Hydrates meta-info of the media file from the
	 * {@link AddStrategyParameters::$exifInfo} attribute of the associated
	 * {@link AddStrategyParameters} object into the associated {@link Photo}
	 * object.
	 *
	 * Meta information is conditionally copied if and only if the target
	 * attribute of the {@link Photo} object is null or empty and the
	 * meta-info is not.
	 * This way this method is usable by {@link AddStandaloneStrategy} and
	 * {@link AddDuplicateStrategy}.
	 * For a freshly created {@link Photo} object (with empty attributes)
	 * all available meta-data is hydrated, but for an already existing
	 * {@link Photo} object existing attributes are not overwritten.
	 */
	protected function hydrateMetadata(): void
	{
		if ($this->photo->title === null) {
			$this->photo->title = $this->parameters->exifInfo->title;
		}
		if ($this->photo->description === null) {
			$this->photo->description = $this->parameters->exifInfo->description;
		}
		if (count($this->photo->tags) === 0) {
			$this->photo->tags = $this->parameters->exifInfo->tags;
		}
		if ($this->photo->type === null) {
			$this->photo->type = $this->parameters->exifInfo->type;
		}
		if ($this->photo->iso === null) {
			$this->photo->iso = $this->parameters->exifInfo->iso;
		}
		if ($this->photo->aperture === null) {
			$this->photo->aperture = $this->parameters->exifInfo->aperture;
		}
		if ($this->photo->make === null) {
			$this->photo->make = $this->parameters->exifInfo->make;
		}
		if ($this->photo->model === null) {
			$this->photo->model = $this->parameters->exifInfo->model;
		}
		if ($this->photo->lens === null) {
			$this->photo->lens = $this->parameters->exifInfo->lens;
		}
		if ($this->photo->shutter === null) {
			$this->photo->shutter = $this->parameters->exifInfo->shutter;
		}
		if ($this->photo->focal === null) {
			$this->photo->focal = $this->parameters->exifInfo->focal;
		}
		if ($this->photo->taken_at === null) {
			$this->photo->taken_at = $this->parameters->exifInfo->taken_at;
		}
		if ($this->photo->latitude === null) {
			$this->photo->latitude = $this->parameters->exifInfo->latitude;
		}
		if ($this->photo->longitude === null) {
			$this->photo->longitude = $this->parameters->exifInfo->longitude;
		}
		if ($this->photo->altitude === null) {
			$this->photo->altitude = $this->parameters->exifInfo->altitude;
		}
		if ($this->photo->img_direction === null) {
			$this->photo->img_direction = $this->parameters->exifInfo->imgDirection;
		}
		if ($this->photo->location === null) {
			$this->photo->location = $this->parameters->exifInfo->location;
		}
		if ($this->photo->live_photo_content_id === null) {
			$this->photo->live_photo_content_id = $this->parameters->exifInfo->livePhotoContentID;
		}
	}

	protected function setParentAndOwnership(): void
	{
		if ($this->parameters->album !== null) {
			$this->photo->album_id = $this->parameters->album->id;
			// Avoid unnecessary DB request, when we access the album of a
			// photo later (e.g. when a notification is sent).
			$this->photo->setRelation('album', $this->parameters->album);
			$this->photo->owner_id = $this->parameters->album->owner_id;
		} else {
			$this->photo->album_id = null;
			// Avoid unnecessary DB request, when we access the album of a
			// photo later (e.g. when a notification is sent).
			$this->photo->setRelation('album', null);
			$this->photo->owner_id = AccessControl::id();
		}
	}

	/**
	 * Moves/copies/symlinks source file to final destination.
	 *
	 * @param MediaFile $sourceFile the source file
	 * @param string    $targetPath the path of the final destination relative
	 *                              to the disk
	 *                              {@link AddBaseStrategy::IMAGE_DISK_NAME}
	 *
	 * @throws MediaFileOperationException
	 * @throws ConfigurationException
	 */
	protected function putSourceIntoFinalDestination(MediaFile $sourceFile, string $targetPath): void
	{
		$targetFile = new FlysystemFile(Storage::disk(self::IMAGE_DISK_NAME), $targetPath);
		$isTargetLocal = $targetFile->getStorageAdapter() instanceof Local;
		if ($this->parameters->importMode->shallImportViaSymlink()) {
			if (!$isTargetLocal) {
				throw new ConfigurationException('Symlinking is only supported on local filesystems');
			}
			if (!($sourceFile instanceof NativeLocalFile)) {
				throw new ConfigurationException('Symlinking is only supported to local files');
			}
			$targetAbsolutePath = $targetFile->getAbsolutePath();
			$sourceAbsolutePath = $sourceFile->getAbsolutePath();
			try {
				symlink($sourceAbsolutePath, $targetAbsolutePath);
			} catch (\Throwable $e) {
				throw new MediaFileOperationException('Could not create symbolic link at "' . $targetAbsolutePath . '" for photo at "' . $sourceAbsolutePath . '"', $e);
			}
		} else {
			try {
				$targetFile->write($sourceFile->read());
				$sourceFile->close();
				if ($this->parameters->importMode->shallDeleteImported()) {
					// This may throw an exception, if the original has been
					// readable, but is not writable
					// In this case, the media file will have been copied, but
					// cannot be "moved".
					try {
						$sourceFile->delete();
					} catch (MediaFileOperationException $e) {
						// If deletion failed, we do not cancel the whole
						// import, but fall back to copy-semantics and
						// log the exception
						report($e);
					}
				}
			} catch (LycheeLogicException $e) {
				// the exception is thrown if read/write/close are invoked
				// in wrong order
				// something we don't do
				throw LycheeAssertionError::createFromUnexpectedException($e);
			}
		}
	}
}
