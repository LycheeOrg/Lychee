<?php

namespace App\Actions\Photo\Strategies;

use App\Contracts\LycheeException;
use App\Contracts\SizeVariantFactory;
use App\Contracts\SizeVariantNamingStrategy;
use App\Exceptions\ConfigurationException;
use App\Exceptions\ImageProcessingException;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Exceptions\Internal\InvalidRotationDirectionException;
use App\Exceptions\Internal\LycheeLogicException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;
use App\Image\FlysystemFile;
use App\Image\ImageHandlerInterface;
use App\Image\MediaFile;
use App\Image\NativeLocalFile;
use App\Image\TemporaryLocalFile;
use App\Metadata\Extractor;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Support\Facades\Storage;

class RotateStrategy extends AddBaseStrategy
{
	protected int $direction;

	/**
	 * @param Photo $photo
	 * @param int   $direction
	 *
	 * @throws MediaFileUnsupportedException     thrown, if $photo cannot be
	 *                                           rotated
	 * @throws InvalidRotationDirectionException thrown if $direction does
	 *                                           neither equal -1 nor 1
	 * @throws IllegalOrderOfOperationException
	 */
	public function __construct(Photo $photo, int $direction)
	{
		// We exploit the "add strategy" here, because rotation of a photo
		// has a lot in common with adding a new photo.
		// We first make a temporary copy of the photo, rotate that copy
		// and then "re-import" that temporary copy into an existing photo
		// model.
		// As we want the temporary file to be moved back into place,
		// we delete the "imported" file and do not want to import via symlink.
		// We do not want to skip duplicates (in case the photo is already a
		// duplicate, we still want to rotate it) and we want to re-sync
		// metadata (after rotation width, height and filesize may have changed).
		//
		// In case the photo has originally been imported as a symbolic link,
		// the photo won't be a symbolic link after rotation, but become an
		// independent file which is detached from the original target of the
		// symbolic link.
		// This is by design.
		// The two alternatives would be:
		//  1. Rotate the original photo which the symlink points to.
		//  2. Bail out with an error message, if the user attempts to rotate
		//     a photo that was imported via a symlink
		// After discussion among the developers, option 1 was considered a
		// no-go and 2 was considered to be too restrictive.
		parent::__construct(
			new AddStrategyParameters(
				new ImportMode(true, false, false, true)
			),
			$photo
		);
		if ($photo->isVideo()) {
			throw new MediaFileUnsupportedException('Rotation of a video is unsupported');
		}
		if ($photo->live_photo_short_path !== null) {
			throw new MediaFileUnsupportedException('Rotation of a live photo is unsupported');
		}
		if ($photo->isRaw()) {
			throw new MediaFileUnsupportedException('Rotation of a raw photo is unsupported');
		}
		// direction is valid?
		if (($direction != 1) && ($direction != -1)) {
			throw new InvalidRotationDirectionException();
		}
		$this->direction = $direction;
	}

	/**
	 * @return Photo
	 *
	 * @throws LycheeException
	 */
	public function do(): Photo
	{
		// Generate a temporary name for the rotated file.
		$oldOriginalSizeVariant = $this->photo->size_variants->getOriginal();
		$oldOriginalWidth = $oldOriginalSizeVariant->width;
		$oldOriginalHeight = $oldOriginalSizeVariant->height;
		$oldChecksum = $this->photo->checksum;
		$origFile = $oldOriginalSizeVariant->getFile();
		$tmpFile = new TemporaryLocalFile($origFile->getExtension());

		// Rotate the image and save result as the temporary file
		/** @var ImageHandlerInterface $imageHandler */
		$imageHandler = resolve(ImageHandlerInterface::class);
		// TODO: If we ever wish to support something else than local files, ImageHandler must work on resource streams, not absolute file names (see ImageHandlerInterface)
		$imageHandler->rotate($origFile->getAbsolutePath(), ($this->direction == 1) ? 90 : -90, $tmpFile->getAbsolutePath());

		// The file size and checksum may have changed after the rotation.
		$this->photo->checksum = Extractor::checksum($tmpFile);
		$this->photo->save();

		// Delete all size variants from current photo, this will also take
		// care of erasing the actual "physical" files from storage and any
		// potential symbolic link which points to one of the original files.
		// This will bring photo entity into the same state as it would be if
		// we were importing a new photo.
		$this->photo->size_variants->deleteAll();
		$origFile = null;

		/** @var SizeVariantNamingStrategy $namingStrategy */
		$namingStrategy = resolve(SizeVariantNamingStrategy::class);
		$namingStrategy->setFallbackExtension($tmpFile->getOriginalExtension());
		/** @var SizeVariantFactory $sizeVariantFactory */
		$sizeVariantFactory = resolve(SizeVariantFactory::class);
		$sizeVariantFactory->init($this->photo, $namingStrategy);

		// Create size variant for rotated original
		// Note that this also creates a different file name than before
		// because the checksum of the photo has changed.
		// Using a different filename allows avoiding caching effects.
		// Sic! Swap width and height here, because the image has been rotated
		$originalFilesize = $tmpFile->getFilesize();
		$newOriginalSizeVariant = $sizeVariantFactory->createOriginal($oldOriginalHeight, $oldOriginalWidth, $originalFilesize);
		$this->putSourceIntoFinalDestination($tmpFile, $newOriginalSizeVariant->short_path);

		// Create remaining size variants
		$newSizeVariants = null;
		try {
			$newSizeVariants = $sizeVariantFactory->createSizeVariants();
			// add new original size variant to collection of newly created
			// size variants; we need this to correctly update the duplicates
			// below
			$newSizeVariants->add($newOriginalSizeVariant);
		} catch (\Throwable $t) {
			// Don't re-throw the exception, because we do not want the
			// import to fail completely only due to missing size variants.
			// There are just too many options why the creation of size
			// variants may fail: the user has uploaded an unsupported file
			// format, GD and Imagick are both not available or disabled
			// by configuration, etc.
			report($t);
		}

		// Clean up factory
		$sizeVariantFactory->cleanup();

		// Deal with duplicates.  We simply update all of them to match.
		$duplicates = Photo::query()
				->where('checksum', '=', $oldChecksum)
				->get();
		/** @var Photo $duplicate */
		foreach ($duplicates as $duplicate) {
			$duplicate->checksum = $this->photo->checksum;
			// Note: It is not correct to simply update the existing size
			// variants of the duplicates.
			// Due to rotation the number and type of size variants may have
			// changed, too.
			// So we actually have to do a 3-way merge and update:
			//  1. delete size variants of the duplicates which do not exist
			//     anymore,
			//  2. update size variants of the duplicates which still exist,
			//     and
			//  3. add new size variants to duplicates which
			// haven't existed before.
			// For simplicity, we simply delete all size variants of the
			// duplicates and re-create them.
			// Deleting the size variants of the duplicates has also the
			// advantage that the actual files are erased from storage.
			$duplicate->size_variants->deleteAll();
			if ($newSizeVariants) {
				/** @var SizeVariant $newSizeVariant */
				foreach ($newSizeVariants as $newSizeVariant) {
					$duplicate->size_variants->create(
						$newSizeVariant->type,
						$newSizeVariant->short_path,
						$newSizeVariant->width,
						$newSizeVariant->height,
						$newSizeVariant->filesize
					);
				}
			}
			$duplicate->save();
		}

		return $this->photo;
	}

	/**
	 * Moves/copies/symlinks source file to final destination.
	 *
	 * TODO: targetPath should be a proper file object
	 *
	 * @param MediaFile $sourceFile     the source file
	 * @param string    $targetPath     the path of the final destination
	 *                                  relative to the disk
	 *                                  {@link AddBaseStrategy::IMAGE_DISK_NAME}
	 * @param bool      $normalizeImage if true, the image is not only
	 *                                  moved/copied from the source to the
	 *                                  target, but processed through
	 *                                  {@link ImageHandler}.
	 *                                  Inter alia, this means the orientation
	 *                                  of the image will be normalized.
	 *                                  The flag has no effect, if the image
	 *                                  shall be imported via a symbolic
	 *                                  link.
	 *
	 * @throws ImageProcessingException
	 * @throws MediaFileOperationException
	 * @throws MediaFileUnsupportedException
	 * @throws ConfigurationException
	 */
	private function putSourceIntoFinalDestination(MediaFile $sourceFile, string $targetPath, bool $normalizeImage = false): void
	{
		$targetFile = new FlysystemFile(Storage::disk(self::IMAGE_DISK_NAME), $targetPath);
		$isTargetLocal = $targetFile->isLocalFile();
		if ($this->parameters->importMode->shallImportViaSymlink()) {
			if (!$isTargetLocal) {
				throw new ConfigurationException('Symlinking is only supported on local filesystems');
			}
			if (!($sourceFile instanceof NativeLocalFile)) {
				throw new ConfigurationException('Symlinking is only supported to local files');
			}
			$targetAbsolutePath = $targetFile->getAbsolutePath();
			$sourceAbsolutePath = $sourceFile->getAbsolutePath();
			if (!symlink($sourceAbsolutePath, $targetAbsolutePath)) {
				throw new MediaFileOperationException('Could not create symbolic link at "' . $targetAbsolutePath . '" for photo at "' . $sourceAbsolutePath . '"');
			}
		} else {
			try {
				if ($normalizeImage) {
					$this->imageHandler->load($sourceFile);
					$this->imageHandler->save($targetFile);
				} else {
					$targetFile->write($sourceFile->read());
					$sourceFile->close();
					$targetFile->close();
				}
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
				assert(false, new \AssertionError('read/write/close must not throw a logic exception', $e->getCode(), $e));
			}
		}
	}
}
