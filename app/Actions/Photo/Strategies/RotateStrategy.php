<?php

namespace App\Actions\Photo\Strategies;

use App\Contracts\LycheeException;
use App\Contracts\SizeVariantFactory;
use App\Contracts\SizeVariantNamingStrategy;
use App\DTO\ImageDimension;
use App\Exceptions\Handler;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Exceptions\Internal\InvalidRotationDirectionException;
use App\Exceptions\Internal\LycheeDomainException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\MediaFileUnsupportedException;
use App\Image\ImageHandler;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;

class RotateStrategy
{
	protected Photo $photo;
	/** @var int either `1` for counter-clock or `-1` for anti counter-clock rotation */
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
		$this->photo = $photo;
		$this->direction = $direction;
	}

	/**
	 * Rotates the photo and its duplicates and re-generates all size variants.
	 *
	 * @return Photo the updated (i.e. rotated) photo
	 *
	 * @throws LycheeException
	 */
	public function do(): Photo
	{
		try {
			// Load the previous original image and rotate it
			$origFile = $this->photo->size_variants->getOriginal()->getFile();
			$image = new ImageHandler();
			$image->load($origFile);
			try {
				$image->rotate(90 * $this->direction);
			} catch (LycheeDomainException $e) {
				assert(false, new \AssertionError('unexpected domain exception for a proper rotation angle', $e));
			}

			// Delete all size variants from current photo, this will also take
			// care of erasing the actual "physical" files from storage and any
			// potential symbolic link which points to one of the original files.
			// This will bring photo entity into the same state as it would be if
			// we were importing a new photo.
			$this->photo->size_variants->deleteAll();

			/** @var SizeVariantNamingStrategy $namingStrategy */
			$namingStrategy = resolve(SizeVariantNamingStrategy::class);
			$namingStrategy->setPhoto($this->photo);
			$namingStrategy->setFallbackExtension($origFile->getOriginalExtension());

			// Create new target file for rotated original size variant,
			// stream it into final place and delete the original file
			$targetFile = $namingStrategy->createFile(SizeVariant::ORIGINAL);
			$streamStat = $image->save($targetFile, true);
			try {
				$origFile->delete();
			} catch (MediaFileOperationException $e) {
				// If deletion failed, we do not cancel the whole rotation
				// operation, but fall back to copy-semantics and log the
				// exception
				Handler::reportSafely($e);
			} finally {
				$origFile = null;
			}

			// The checksum has been changed due to rotation.
			$oldChecksum = $this->photo->checksum;
			$this->photo->checksum = $streamStat->checksum;
			$this->photo->save();

			// Re-create original size variant of photo
			$newOriginalSizeVariant = $this->photo->size_variants->create(
				SizeVariant::ORIGINAL,
				$targetFile->getRelativePath(),
				$image->getDimensions(),
				$streamStat->bytes
			);

			// Re-create remaining size variants
			/** @var SizeVariantFactory $sizeVariantFactory */
			$sizeVariantFactory = resolve(SizeVariantFactory::class);
			$sizeVariantFactory->init($this->photo, $image, $namingStrategy);
			try {
				$newSizeVariants = $sizeVariantFactory->createSizeVariants();
			} catch (\Throwable $t) {
				// Don't re-throw the exception, because we do not want the
				// rotation operation to fail completely only due to missing size
				// variants.
				// There are just too many options why the creation of size
				// variants may fail.
				Handler::reportSafely($t);
				$newSizeVariants = new Collection();
			}
			// Add new original size variant to collection of newly created
			// size variants; we need this to correctly update the duplicates
			// below
			$newSizeVariants->add($newOriginalSizeVariant);

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
				/** @var SizeVariant $newSizeVariant */
				foreach ($newSizeVariants as $newSizeVariant) {
					$duplicate->size_variants->create(
						$newSizeVariant->type,
						$newSizeVariant->short_path,
						new ImageDimension($newSizeVariant->width, $newSizeVariant->height),
						$newSizeVariant->filesize
					);
				}
				$duplicate->save();
			}

			return $this->photo;
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s container component', $e);
		}
	}
}
