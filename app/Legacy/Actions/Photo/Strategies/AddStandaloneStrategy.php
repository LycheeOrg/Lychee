<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\Actions\Photo\Strategies;

use App\Actions\Diagnostics\Pipes\Checks\BasicPermissionCheck;
use App\Assets\Features;
use App\Contracts\Exceptions\LycheeException;
use App\Contracts\Image\ImageHandlerInterface;
use App\Contracts\Image\StreamStats;
use App\Contracts\Models\AbstractSizeVariantNamingStrategy;
use App\Contracts\Models\SizeVariantFactory;
use App\DTO\ImageDimension;
use App\DTO\ImportParam;
use App\Enum\SizeVariantType;
use App\Exceptions\ConfigurationException;
use App\Exceptions\Handler;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\MediaFileOperationException;
use App\Image\Files\FlysystemFile;
use App\Image\Files\NativeLocalFile;
use App\Image\Files\TemporaryLocalFile;
use App\Image\Handlers\GoogleMotionPictureHandler;
use App\Image\Handlers\ImageHandler;
use App\Image\Handlers\VideoHandler;
use App\Image\PlaceholderEncoder;
use App\Image\StreamStat;
use App\Jobs\UploadSizeVariantToS3Job;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Contracts\Container\BindingResolutionException;

class AddStandaloneStrategy extends AbstractAddStrategy
{
	protected ?ImageHandlerInterface $sourceImage;
	protected NativeLocalFile $sourceFile;
	protected AbstractSizeVariantNamingStrategy $namingStrategy;

	/**
	 * @throws FrameworkException
	 */
	public function __construct(ImportParam $parameters, NativeLocalFile $sourceFile)
	{
		try {
			parent::__construct($parameters, new Photo());
			// We already set the timestamps (`created_at`, `updated_at`) on
			// initialization time, not save time.
			// This keeps the creation timestamps ordered as the images are
			// uploaded/imported.
			// This should be the most consistent/expected behaviour.
			// Otherwise, the creation time would reflect the point of time when
			// Lychee has finished processing the image (rotated, cropped,
			// generated thumbnails).
			// This might lead to "race conditions", i.e. some images might
			// outpace each other.
			// This would not lead to data loss or worse, but images might
			// appear in a different order than users expect.
			$this->photo->updateTimestamps();
			$this->sourceImage = null;
			$this->sourceFile = $sourceFile;
			$this->namingStrategy = resolve(AbstractSizeVariantNamingStrategy::class);
			$this->namingStrategy->setPhoto($this->photo);
			$this->namingStrategy->setExtension(
				$this->sourceFile->getOriginalExtension()
			);
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s container component', $e);
		}
	}

	/**
	 * @return Photo
	 *
	 * @throws LycheeException
	 */
	public function do(): Photo
	{
		// Create and save "bare" photo object without size variants
		$this->hydrateMetadata();
		$this->photo->is_starred = $this->parameters->is_starred;
		$this->setParentAndOwnership();

		// Unfortunately, we must read the entire file once to create the
		// true, original checksum.
		// It does **not** suffice to use the stream statistics, when the
		// image file is loaded, because we cannot guarantee that the image
		// loader reads the file entirely in one pass.
		//  a) The image loader may decide to seek in the file, skip
		//     certain parts (like EXIF information), re-read chunks of the
		//     file multiple times or out-of-order.
		//  b) The image loader may not load the entire file, if the image
		//     stream is shorter than the file and followed by additional
		//     non-image information (e.g. as it is the case for Google
		//     Live Photos)
		$this->photo->original_checksum = StreamStat::createFromLocalFile($this->sourceFile)->checksum;

		try {
			try {
				if ($this->photo->isVideo()) {
					$videoHandler = new VideoHandler();
					$videoHandler->load($this->sourceFile);
					$position = is_numeric($this->photo->aperture) ? floatval($this->photo->aperture) / 2 : 0.0;
					$this->sourceImage = $videoHandler->extractFrame($position);
				} else {
					// Load source image if it is a supported photo format
					$this->sourceImage = new ImageHandler();
					$this->sourceImage->load($this->sourceFile);
				}
			} catch (\Throwable $e) {
				// This may happen for videos if FFmpeg is not available to
				// extract a still frame, or for raw files (Imagick may be
				// able to convert them to jpeg, but there are no
				// guarantees, and Imagick may not be available).
				$this->sourceImage = null;

				// Log an error without failing.
				Handler::reportSafely($e);
			}

			// Handle Google Motion Pictures
			// We must extract the video stream from the original (local)
			// file and stash it away, before the original file is moved into
			// its (potentially remote) final position
			if ($this->parameters->exifInfo->microVideoOffset !== 0) {
				try {
					$tmpVideoFile = new TemporaryLocalFile(GoogleMotionPictureHandler::FINAL_VIDEO_FILE_EXTENSION, $this->sourceFile->getBasename());
					$gmpHandler = new GoogleMotionPictureHandler();
					$gmpHandler->load($this->sourceFile, $this->parameters->exifInfo->microVideoOffset);
					$gmpHandler->saveVideoStream($tmpVideoFile);
				} catch (\Throwable $e) {
					Handler::reportSafely($e);
					$tmpVideoFile = null;
				}
			} else {
				$tmpVideoFile = null;
			}

			// Create target file and symlink/copy/move source file to
			// target.
			// If import strategy request to delete the source file.
			// `$this->sourceFile` will be deleted after this step.
			// But `$this->sourceImage` remains in memory.
			$targetFile = $this->namingStrategy->createFile(SizeVariantType::ORIGINAL);
			$streamStat = $this->putSourceIntoFinalDestination($targetFile);

			// If we have a temporary video file from a Google Motion Picture,
			// we must move the preliminary extracted video file next to the
			// final target file
			if ($tmpVideoFile !== null) {
				// @TODO S3 How should live videos be handled?
				$videoTargetPath =
					pathinfo($targetFile->getRelativePath(), PATHINFO_DIRNAME) .
					'/' .
					pathinfo($targetFile->getRelativePath(), PATHINFO_FILENAME) .
					$tmpVideoFile->getExtension();
				$videoTargetFile = new FlysystemFile($targetFile->getDisk(), $videoTargetPath);
				$videoTargetFile->write($tmpVideoFile->read());
				$this->photo->live_photo_short_path = $videoTargetFile->getRelativePath();
				$tmpVideoFile->close();
				$tmpVideoFile->delete();
				$tmpVideoFile = null;
			}

			// The original and final checksum may differ, if the photo has
			// been rotated by `putSourceIntoFinalDestination` while being
			// moved into final position.
			$this->photo->checksum = $streamStat->checksum;
			$this->photo->save();

			// Create original size variant of photo
			// If the image has been loaded (and potentially auto-rotated)
			// take the dimension from the image.
			// As a fallback for media files from which no image could be extracted (e.g. unsupported file formats) we use the EXIF data.
			$imageDim = $this->sourceImage?->isLoaded() ?
				$this->sourceImage->getDimensions() :
				new ImageDimension($this->parameters->exifInfo->width, $this->parameters->exifInfo->height);
			$originalVariant = $this->photo->size_variants->create(
				SizeVariantType::ORIGINAL,
				$targetFile->getRelativePath(),
				$imageDim,
				$streamStat->bytes
			);
		} catch (LycheeException $e) {
			// If source file could not be put into final destination, remove
			// freshly created photo from DB to avoid having "zombie" entries.
			try {
				$this->photo->delete();
			} catch (\Throwable) {
				// Sic! If anything goes wrong here, we still throw the original exception
			}
			throw $e;
		}

		// Create remaining size variants if we were able to successfully
		// extract a reference image above
		if ($this->sourceImage?->isLoaded()) {
			try {
				/** @var SizeVariantFactory $sizeVariantFactory */
				$sizeVariantFactory = resolve(SizeVariantFactory::class);
				$sizeVariantFactory->init($this->photo, $this->sourceImage, $this->namingStrategy);
				$variants = $sizeVariantFactory->createSizeVariants();
				$variants->push($originalVariant);

				$placeholder = $variants->firstWhere('type', SizeVariantType::PLACEHOLDER);
				if ($placeholder !== null) {
					$placeholderEncoder = new PlaceholderEncoder();
					$placeholderEncoder->do($placeholder);
				}

				if (Features::active('use-s3')) {
					// If enabled, upload all size variants to the remote bucket and delete the local files after that
					$variants->each(function (SizeVariant $variant) {
						if (Configs::getValueAsBool('use_job_queues')) {
							UploadSizeVariantToS3Job::dispatch($variant);
						} else {
							$job = new UploadSizeVariantToS3Job($variant);
							$job->handle();
						}
					});
				}
			} catch (\Throwable $t) {
				// Don't re-throw the exception, because we do not want the
				// import to fail completely only due to missing size variants.
				// There are just too many options why the creation of size
				// variants may fail.
				Handler::reportSafely($t);
			}
		}

		return $this->photo;
	}

	/**
	 * Moves/copies/symlinks source file to final destination and
	 * normalizes orientation, if necessary.
	 *
	 * Note, {@link AddStandaloneStrategy::$sourceFile} and
	 * {@link AddStandaloneStrategy::$sourceImage} must be set before this
	 * method is called.
	 *
	 * If import via symbolic link is requested, then a symbolic link
	 * from `$targetFile` to {@link AddStandaloneStrategy::$sourceFile} is
	 * created.
	 * Otherwise the content of {@link AddStandaloneStrategy::$sourceFile}
	 * is physically copied/moved into `$targetFile`.
	 *
	 * If the source file requires normalization, then
	 * {@link AddStandaloneStrategy::$sourceImage} is saved to `$targetFile`.
	 * This step implicitly corrects the orientation.
	 * Otherwise, the original byte stream from
	 * {@link AddStandaloneStrategy::$sourceFile} is written to `$targetFile`
	 * without modifications.
	 *
	 * @param FlysystemFile $targetFile the target file
	 *
	 * @return StreamStats statistics about the final file, may differ from
	 *                     the source file due to normalization of orientation
	 *
	 * @throws MediaFileOperationException
	 * @throws ConfigurationException
	 */
	private function putSourceIntoFinalDestination(FlysystemFile $targetFile): StreamStats
	{
		try {
			if ($this->parameters->importMode->shallImportViaSymlink) {
				if (!$targetFile->isLocalFile()) {
					throw new ConfigurationException('Symlinking is only supported on local filesystems');
				}
				$targetPath = $targetFile->toLocalFile()->getPath();
				$sourcePath = $this->sourceFile->getRealPath();
				// For symlinks we must manually create a non-existing
				// parent directory.
				// This mimics the behaviour of Flysystem for regular files.
				$targetDirectory = pathinfo($targetPath, PATHINFO_DIRNAME);
				if (!is_dir($targetDirectory)) {
					$umask = \umask(0);
					\Safe\mkdir($targetDirectory, BasicPermissionCheck::getConfiguredDirectoryPerm(), true);
					\umask($umask);
				}
				\Safe\symlink($sourcePath, $targetPath);
				$streamStat = StreamStat::createFromLocalFile($this->sourceFile);
			} else {
				$shallNormalize = Configs::getValueAsBool('auto_fix_orientation') &&
					$this->sourceImage !== null &&
					$this->parameters->exifInfo->orientation !== 1;

				if ($shallNormalize) {
					// Saving the loaded image to the final target normalizes
					// the image orientation. This comes at the cost that
					// the image is re-encoded and hence its quality might
					// be reduced.
					$streamStat = $this->sourceImage->save($targetFile, true);
				} else {
					// If the image does not require normalization the
					// unaltered source file is copied to the final target.
					// Avoiding a re-encoding prevents any potential quality
					// loss.
					$streamStat = $targetFile->write($this->sourceFile->read(), true);
					$this->sourceFile->close();
					$targetFile->close();
				}
				if ($this->parameters->importMode->shallDeleteImported) {
					// This may throw an exception, if the original has been
					// readable, but is not writable
					// In this case, the media file will have been copied, but
					// cannot be "moved".
					try {
						$this->sourceFile->delete();
					} catch (MediaFileOperationException $e) {
						// If deletion failed, we do not cancel the whole
						// import, but fall back to copy-semantics and
						// log the exception
						Handler::reportSafely($e);
					}
				}
			}

			return $streamStat;
		} catch (\ErrorException $e) {
			throw new MediaFileOperationException('Could move/copy/symlink source file to final destination', $e);
		}
	}
}
