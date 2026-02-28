<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models\Extensions;

use App\Actions\SizeVariant\Delete;
use App\DTO\ImageDimension;
use App\Enum\SizeVariantType;
use App\Enum\StorageDiskType;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Exceptions\Internal\InvalidSizeVariantException;
use App\Exceptions\Internal\LycheeAssertionError;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\ModelDBException;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Collection as BaseCollection;

/**
 * Class SizeVariants.
 */
class SizeVariants
{
	/** @var Photo the parent object this object is tied to */
	private Photo $photo;

	private ?SizeVariant $raw = null;
	private ?SizeVariant $original = null;
	private ?SizeVariant $medium2x = null;
	private ?SizeVariant $medium = null;
	private ?SizeVariant $small2x = null;
	private ?SizeVariant $small = null;
	private ?SizeVariant $thumb2x = null;
	private ?SizeVariant $thumb = null;
	private ?SizeVariant $placeholder = null;

	/**
	 * SizeVariants constructor.
	 *
	 * @param Photo                            $photo         the parent object this object is tied to
	 * @param Collection<int,SizeVariant>|null $size_variants a collection of size variants
	 *
	 * @throws LycheeInvalidArgumentException thrown if the photo and the collection of size variants don't
	 *                                        belong together
	 */
	public function __construct(Photo $photo, ?Collection $size_variants = null)
	{
		$this->photo = $photo;
		if ($size_variants !== null) {
			foreach ($size_variants as $size_variant) {
				$this->add($size_variant);
			}
		}
	}

	/*
	 * @return void
	 *
	 * @throws LycheeInvalidArgumentException thrown if ID of owning photo does not match
	 */
	public function add(SizeVariant $size_variant): void
	{
		if ($size_variant->photo_id !== $this->photo->id) {
			// @codeCoverageIgnoreStart
			throw new LycheeInvalidArgumentException('ID of owning photo does not match');
			// @codeCoverageIgnoreEnd
		}
		$size_variant->setRelation('photo', $this->photo);
		$candidate = $this->getSizeVariant($size_variant->type);

		if ($candidate !== null && $candidate->id !== $size_variant->id) {
			// @codeCoverageIgnoreStart
			throw new LycheeInvalidArgumentException('Another size variant of the same type has already been added');
			// @codeCoverageIgnoreEnd
		}

		match ($size_variant->type) {
			SizeVariantType::RAW => $this->raw = $size_variant,
			SizeVariantType::ORIGINAL => $this->original = $size_variant,
			SizeVariantType::MEDIUM2X => $this->medium2x = $size_variant,
			SizeVariantType::MEDIUM => $this->medium = $size_variant,
			SizeVariantType::SMALL2X => $this->small2x = $size_variant,
			SizeVariantType::SMALL => $this->small = $size_variant,
			SizeVariantType::THUMB2X => $this->thumb2x = $size_variant,
			SizeVariantType::THUMB => $this->thumb = $size_variant,
			SizeVariantType::PLACEHOLDER => $this->placeholder = $size_variant,
		};
	}

	/**
	 * Return all SizeVariants as a collection.
	 *
	 * @return BaseCollection<int,SizeVariant|null>
	 */
	public function toCollection(): BaseCollection
	{
		/** @phpstan-ignore return.type (BaseCollection is not covariant...) */
		return collect([
			$this->raw,
			$this->original,
			$this->medium2x,
			$this->medium,
			$this->small2x,
			$this->small,
			$this->thumb2x,
			$this->thumb,
			$this->placeholder,
		]);
	}

	/**
	 * Returns the requested size variant of the photo.
	 *
	 * @param SizeVariantType $size_variant_type the type of the size variant
	 *
	 * @return SizeVariant|null The size variant
	 *
	 * @throws InvalidSizeVariantException
	 */
	public function getSizeVariant(SizeVariantType $size_variant_type): ?SizeVariant
	{
		return match ($size_variant_type) {
			SizeVariantType::RAW => $this->raw,
			SizeVariantType::ORIGINAL => $this->original,
			SizeVariantType::MEDIUM2X => $this->medium2x,
			SizeVariantType::MEDIUM => $this->medium,
			SizeVariantType::SMALL2X => $this->small2x,
			SizeVariantType::SMALL => $this->small,
			SizeVariantType::THUMB2X => $this->thumb2x,
			SizeVariantType::THUMB => $this->thumb,
			SizeVariantType::PLACEHOLDER => $this->placeholder,
		};
	}

	public function getOriginal(): ?SizeVariant
	{
		return $this->original;
	}

	public function getRaw(): ?SizeVariant
	{
		return $this->raw;
	}

	/**
	 * Get Medium2x or fallback to Medium.
	 */
	public function getMedium2x(): ?SizeVariant
	{
		return $this->medium2x;
	}

	/**
	 * get Medium or fallback to Original.
	 */
	public function getMedium(): ?SizeVariant
	{
		return $this->medium;
	}

	/**
	 * Get Small2x or fallback to Small.
	 */
	public function getSmall2x(): ?SizeVariant
	{
		return $this->small2x;
	}

	public function getSmall(): ?SizeVariant
	{
		return $this->small;
	}

	public function getThumb2x(): ?SizeVariant
	{
		return $this->thumb2x;
	}

	public function getThumb(): ?SizeVariant
	{
		return $this->thumb;
	}

	public function getPlaceholder(): ?SizeVariant
	{
		return $this->placeholder;
	}

	/**
	 * Creates a new instance of {@link \App\Models\SizeVariant} for the
	 * associated photo and persists it to DB.
	 *
	 * @param SizeVariantType $size_variant_type the type of the desired size variant;
	 * @param string          $short_path        the short path of the media file this
	 *                                           size variant shall point to
	 * @param ImageDimension  $dim               the width of the size variant
	 * @param int             $filesize          the filesize of the size variant
	 *
	 * @return SizeVariant The newly created and persisted size variant
	 *
	 * @throws IllegalOrderOfOperationException
	 * @throws ModelDBException
	 *
	 * @disregard P1006
	 */
	public function create(SizeVariantType $size_variant_type, string $short_path, ImageDimension $dim, int $filesize): SizeVariant
	{
		if (!$this->photo->exists) {
			// @codeCoverageIgnoreStart
			throw new IllegalOrderOfOperationException('Cannot create a size variant for a photo whose id is not yet persisted to DB');
			// @codeCoverageIgnoreEnd
		}
		try {
			$result = SizeVariant::create([
				'photo_id' => $this->photo->id,
				'storage_disk' => StorageDiskType::LOCAL,
				'type' => $size_variant_type,
				'short_path' => $short_path,
				'width' => $dim->width,
				'height' => $dim->height,
				'filesize' => $filesize,
				'ratio' => $dim->getRatio(),
			]);
			/** @disregard P1006 */
			$this->add($result);

			return $result;
			// @codeCoverageIgnoreStart
		} catch (LycheeInvalidArgumentException $e) {
			// thrown by ::add(), if  $result->photo_id !==  $this->photo->id,
			// but we know that we assert that
			throw LycheeAssertionError::createFromUnexpectedException($e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Deletes all size variants incl. the files from storage.
	 *
	 * @throws ModelDBException
	 * @throws MediaFileOperationException
	 */
	public function deleteAll(): void
	{
		$ids = [
			$this->raw?->id,
			$this->original?->id,
			$this->medium2x?->id,
			$this->medium?->id,
			$this->small2x?->id,
			$this->small?->id,
			$this->thumb2x?->id,
			$this->thumb?->id,
			$this->placeholder?->id,
		];

		$this->raw = null;
		$this->original = null;
		$this->medium2x = null;
		$this->medium = null;
		$this->small2x = null;
		$this->small = null;
		$this->thumb2x = null;
		$this->thumb = null;
		$this->placeholder = null;

		(new Delete())->do(array_diff($ids, [null]));
	}

	/**
	 * Returns true if at least one version of medium is not null.
	 */
	public function hasMedium(): bool
	{
		return $this->medium !== null || $this->medium2x !== null;
	}
}