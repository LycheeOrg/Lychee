<?php

namespace App\Models\Extensions;

use App\Actions\SizeVariant\Delete;
use App\DTO\AbstractDTO;
use App\DTO\ImageDimension;
use App\Enum\SizeVariantType;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Exceptions\Internal\InvalidSizeVariantException;
use App\Exceptions\Internal\LycheeAssertionError;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\ModelDBException;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class SizeVariants.
 */
class SizeVariants extends AbstractDTO
{
	/** @var Photo the parent object this object is tied to */
	private Photo $photo;

	public ?SizeVariant $original = null;
	public ?SizeVariant $medium2x = null;
	public ?SizeVariant $medium = null;
	public ?SizeVariant $small2x = null;
	public ?SizeVariant $small = null;
	public ?SizeVariant $thumb2x = null;
	public ?SizeVariant $thumb = null;

	/**
	 * SizeVariants constructor.
	 *
	 * @param Photo                        $photo        the parent object
	 *                                                   this object is tied to
	 * @param Collection<SizeVariant>|null $sizeVariants a collection of size
	 *                                                   variants
	 *
	 * @throws LycheeInvalidArgumentException thrown if the photo and the
	 *                                        collection of size variants don't
	 *                                        belong together
	 */
	public function __construct(Photo $photo, ?Collection $sizeVariants = null)
	{
		$this->photo = $photo;
		if ($sizeVariants !== null) {
			/** @var SizeVariant $sizeVariant */
			foreach ($sizeVariants as $sizeVariant) {
				$this->add($sizeVariant);
			}
		}
	}

	/**
	 * @param SizeVariant $sizeVariant
	 *
	 * @return void
	 *
	 * @throws LycheeInvalidArgumentException thrown if ID of owning photo
	 *                                        does not match
	 */
	public function add(SizeVariant $sizeVariant): void
	{
		if ($sizeVariant->photo_id !== $this->photo->id) {
			throw new LycheeInvalidArgumentException('ID of owning photo does not match');
		}
		$sizeVariant->setRelation('photo', $this->photo);
		$candidate = $this->getSizeVariant($sizeVariant->type);

		if ($candidate !== null && $candidate->id !== $sizeVariant->id) {
			throw new LycheeInvalidArgumentException('Another size variant of the same type has already been added');
		}

		match ($sizeVariant->type) {
			SizeVariantType::ORIGINAL => $this->original = $sizeVariant,
			SizeVariantType::MEDIUM2X => $this->medium2x = $sizeVariant,
			SizeVariantType::MEDIUM => $this->medium = $sizeVariant,
			SizeVariantType::SMALL2X => $this->small2x = $sizeVariant,
			SizeVariantType::SMALL => $this->small = $sizeVariant,
			SizeVariantType::THUMB2X => $this->thumb2x = $sizeVariant,
			SizeVariantType::THUMB => $this->thumb = $sizeVariant,
		};
	}

	/**
	 * Serializes this object into an array.
	 *
	 * @return array<string, array|null> The serialized properties of this object
	 */
	public function toArray(): array
	{
		return [
			SizeVariantType::ORIGINAL->name() => $this->original?->toArray(),
			SizeVariantType::MEDIUM2X->name() => $this->medium2x?->toArray(),
			SizeVariantType::MEDIUM->name() => $this->medium?->toArray(),
			SizeVariantType::SMALL2X->name() => $this->small2x?->toArray(),
			SizeVariantType::SMALL->name() => $this->small?->toArray(),
			SizeVariantType::THUMB2X->name() => $this->thumb2x?->toArray(),
			SizeVariantType::THUMB->name() => $this->thumb?->toArray(),
		];
	}

	/**
	 * Returns the requested size variant of the photo.
	 *
	 * @param SizeVariantType $sizeVariantType the type of the size variant
	 *
	 * @return SizeVariant|null The size variant
	 *
	 * @throws InvalidSizeVariantException
	 */
	public function getSizeVariant(SizeVariantType $sizeVariantType): ?SizeVariant
	{
		return match ($sizeVariantType) {
			SizeVariantType::ORIGINAL => $this->original,
			SizeVariantType::MEDIUM2X => $this->medium2x,
			SizeVariantType::MEDIUM => $this->medium,
			SizeVariantType::SMALL2X => $this->small2x,
			SizeVariantType::SMALL => $this->small,
			SizeVariantType::THUMB2X => $this->thumb2x,
			SizeVariantType::THUMB => $this->thumb
		};
	}

	public function getOriginal(): ?SizeVariant
	{
		return $this->original;
	}

	public function getMedium(): ?SizeVariant
	{
		return $this->medium;
	}

	public function getThumb2x(): ?SizeVariant
	{
		return $this->thumb2x;
	}

	public function getThumb(): ?SizeVariant
	{
		return $this->thumb;
	}

	/**
	 * Creates a new instance of {@link \App\Models\SizeVariant} for the
	 * associated photo and persists it to DB.
	 *
	 * @param SizeVariantType $sizeVariantType the type of the desired size variant;
	 * @param string          $shortPath       the short path of the media file this
	 *                                         size variant shall point to
	 * @param ImageDimension  $dim             the width of the size variant
	 * @param int             $filesize        the filesize of the size variant
	 *
	 * @return SizeVariant The newly created and persisted size variant
	 *
	 * @throws IllegalOrderOfOperationException
	 * @throws ModelDBException
	 */
	public function create(SizeVariantType $sizeVariantType, string $shortPath, ImageDimension $dim, int $filesize): SizeVariant
	{
		if (!$this->photo->exists) {
			throw new IllegalOrderOfOperationException('Cannot create a size variant for a photo whose id is not yet persisted to DB');
		}
		try {
			$result = new SizeVariant();
			$result->photo_id = $this->photo->id;
			$result->type = $sizeVariantType;
			$result->short_path = $shortPath;
			$result->width = $dim->width;
			$result->height = $dim->height;
			$result->filesize = $filesize;
			$result->save();
			$this->add($result);

			return $result;
		} catch (LycheeInvalidArgumentException $e) {
			// thrown by ::add(), if  $result->photo_id !==  $this->photo->id,
			// but we know that we assert that
			throw LycheeAssertionError::createFromUnexpectedException($e);
		}
	}

	/**
	 * Deletes all size variants incl. the files from storage.
	 *
	 * @return void
	 *
	 * @throws ModelDBException
	 * @throws MediaFileOperationException
	 */
	public function deleteAll(): void
	{
		$ids = [
			$this->original?->id,
			$this->medium2x?->id,
			$this->medium?->id,
			$this->small2x?->id,
			$this->small?->id,
			$this->thumb2x?->id,
			$this->thumb?->id,
		];

		$this->original = null;
		$this->medium2x = null;
		$this->medium = null;
		$this->small2x = null;
		$this->small = null;
		$this->thumb2x = null;
		$this->thumb = null;

		(new Delete())->do(array_diff($ids, [null]))->do();
	}

	/**
	 * @throws ModelDBException
	 * @throws IllegalOrderOfOperationException
	 */
	public function replicate(Photo $duplicatePhoto): SizeVariants
	{
		$duplicate = new SizeVariants($duplicatePhoto);
		self::replicateSizeVariant($duplicate, $this->original);
		self::replicateSizeVariant($duplicate, $this->medium2x);
		self::replicateSizeVariant($duplicate, $this->medium);
		self::replicateSizeVariant($duplicate, $this->small2x);
		self::replicateSizeVariant($duplicate, $this->small);
		self::replicateSizeVariant($duplicate, $this->thumb2x);
		self::replicateSizeVariant($duplicate, $this->thumb);

		return $duplicate;
	}

	/**
	 * @throws ModelDBException
	 * @throws IllegalOrderOfOperationException
	 */
	private static function replicateSizeVariant(SizeVariants $duplicate, ?SizeVariant $sizeVariant): void
	{
		if ($sizeVariant !== null) {
			$duplicate->create(
				$sizeVariant->type,
				$sizeVariant->short_path,
				new ImageDimension($sizeVariant->width, $sizeVariant->height),
				$sizeVariant->filesize
			);
		}
	}

	/**
	 * Returns true if at least one version of medium is not null.
	 *
	 * @return bool
	 */
	public function hasMedium(): bool
	{
		return $this->medium2x !== null || $this->medium !== null;
	}
}
