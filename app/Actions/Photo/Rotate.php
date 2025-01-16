<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Photo;

use App\Contracts\Exceptions\LycheeException;
use App\Contracts\Models\AbstractSizeVariantNamingStrategy;
use App\Contracts\Models\SizeVariantFactory;
use App\DTO\ImageDimension;
use App\Enum\SizeVariantType;
use App\Exceptions\Handler;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Exceptions\Internal\InvalidRotationDirectionException;
use App\Exceptions\Internal\LycheeAssertionError;
use App\Exceptions\Internal\LycheeDomainException;
use App\Exceptions\MediaFileUnsupportedException;
use App\Image\Files\FlysystemFile;
use App\Image\Handlers\ImageHandler;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;

class Rotate
{
	protected Photo $photo;
	/** @var int either `1` for counterclockwise or `-1` for clockwise rotation */
	protected int $direction;
	protected FlysystemFile $sourceFile;
	protected AbstractSizeVariantNamingStrategy $namingStrategy;

	/**
	 * @param Photo $photo
	 * @param int   $direction
	 *
	 * @throws MediaFileUnsupportedException     thrown, if rotation of $photo
	 *                                           is not supported
	 * @throws InvalidRotationDirectionException thrown if $direction neither
	 *                                           equals -1 nor 1
	 * @throws IllegalOrderOfOperationException
	 * @throws FrameworkException
	 */
	public function __construct(Photo $photo, int $direction)
	{
		try {
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
			if (($direction !== 1) && ($direction !== -1)) {
				throw new InvalidRotationDirectionException();
			}
			$this->photo = $photo;
			$this->direction = $direction;
			$this->sourceFile = $this->photo->size_variants->getOriginal()->getFile();
			$this->namingStrategy = resolve(AbstractSizeVariantNamingStrategy::class);
			$this->namingStrategy->setPhoto($this->photo);
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s container component', $e);
		}
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
		// Load the previous original image and rotate it
		$image = new ImageHandler();
		$image->load($this->sourceFile);
		try {
			$image->rotate(90 * $this->direction);
		} catch (LycheeDomainException $e) {
			throw LycheeAssertionError::createFromUnexpectedException($e);
		}

		// Delete all size variants from current photo, this will also take
		// care of erasing the actual "physical" files from storage and any
		// potential symbolic link which points to one of the original files.
		// This will bring photo entity into the same state as it would be if
		// we were importing a new photo.
		// This also deletes the original size variant
		$this->photo->size_variants->deleteAll();

		// We reset the photo of the naming strategy after the size
		// variants have been deleted, in case the naming strategy has based
		// its choice on the existing size variants.
		// As the photo has no size variants anymore, we must set the
		// extension manually from the source file we saved earlier.
		$this->namingStrategy->setPhoto($this->photo);
		$this->namingStrategy->setExtension($this->sourceFile->getExtension());

		// Create new target file for rotated original size variant,
		// and stream it into the final place
		$targetFile = $this->namingStrategy->createFile(SizeVariantType::ORIGINAL);
		$streamStat = $image->save($targetFile, true);

		// The checksum has been changed due to rotation.
		$oldChecksum = $this->photo->checksum;
		$this->photo->checksum = $streamStat->checksum;
		$this->photo->save();

		// Re-create original size variant of photo
		$newOriginalSizeVariant = $this->photo->size_variants->create(
			SizeVariantType::ORIGINAL,
			$targetFile->getRelativePath(),
			$image->getDimensions(),
			$streamStat->bytes
		);

		// Re-create remaining size variants
		try {
			/** @var SizeVariantFactory $sizeVariantFactory */
			$sizeVariantFactory = resolve(SizeVariantFactory::class);
			$sizeVariantFactory->init($this->photo, $image, $this->namingStrategy);
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
	}
}
