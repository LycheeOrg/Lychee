<?php

namespace App\Models\Extensions;

use App\Actions\SizeVariant\Delete;
use App\DTO\DTO;
use App\DTO\ImageDimension;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use App\Exceptions\Internal\InvalidSizeVariantException;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Exceptions\MediaFileOperationException;
use App\Exceptions\ModelDBException;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class SizeVariants.
 */
class SizeVariants extends DTO
{
	/** @var Photo the parent object this object is tied to */
	private Photo $photo;

	private ?SizeVariant $original = null;
	private ?SizeVariant $medium2x = null;
	private ?SizeVariant $medium = null;
	private ?SizeVariant $small2x = null;
	private ?SizeVariant $small = null;
	private ?SizeVariant $thumb2x = null;
	private ?SizeVariant $thumb = null;

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
		if ($sizeVariants) {
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

		switch ($sizeVariant->type) {
			case SizeVariant::ORIGINAL:
				$ref = &$this->original;
				break;
			case SizeVariant::MEDIUM2X:
				$ref = &$this->medium2x;
				break;
			case SizeVariant::MEDIUM:
				$ref = &$this->medium;
				break;
			case SizeVariant::SMALL2X:
				$ref = &$this->small2x;
				break;
			case SizeVariant::SMALL:
				$ref = &$this->small;
				break;
			case SizeVariant::THUMB2X:
				$ref = &$this->thumb2x;
				break;
			case SizeVariant::THUMB:
				$ref = &$this->thumb;
				break;
			default:
				throw new LycheeInvalidArgumentException('size variant ' . $sizeVariant . 'invalid');
		}

		if ($ref && $ref->id !== $sizeVariant->id) {
			throw new LycheeInvalidArgumentException('Another size variant of the same type has already been added');
		}
		$ref = $sizeVariant;
	}

	/**
	 * Serializes this object into an array.
	 *
	 * @return array The serialized properties of this object
	 */
	public function toArray(): array
	{
		return [
			'original' => $this->original?->toArray(),
			'medium2x' => $this->medium2x?->toArray(),
			'medium' => $this->medium?->toArray(),
			'small2x' => $this->small2x?->toArray(),
			'small' => $this->small?->toArray(),
			'thumb2x' => $this->thumb2x?->toArray(),
			'thumb' => $this->thumb?->toArray(),
		];
	}

	/**
	 * Returns the requested size variant of the photo.
	 *
	 * @param int $sizeVariantType the type of the size variant; allowed
	 *                             values are:
	 *                             {@link SizeVariant::ORIGINAL},
	 *                             {@link SizeVariant::MEDIUM2X},
	 *                             {@link SizeVariant::MEDIUM2},
	 *                             {@link SizeVariant::SMALL2X},
	 *                             {@link SizeVariant::SMALL},
	 *                             {@link SizeVariant::THUMB2X}, and
	 *                             {@link SizeVariant::THUMB}
	 *
	 * @return SizeVariant|null The size variant
	 *
	 * @throws InvalidSizeVariantException
	 */
	public function getSizeVariant(int $sizeVariantType): ?SizeVariant
	{
		return match ($sizeVariantType) {
			SizeVariant::ORIGINAL => $this->original,
			SizeVariant::MEDIUM2X => $this->medium2x,
			SizeVariant::MEDIUM => $this->medium,
			SizeVariant::SMALL2X => $this->small2x,
			SizeVariant::SMALL => $this->small,
			SizeVariant::THUMB2X => $this->thumb2x,
			SizeVariant::THUMB => $this->thumb,
			default => throw new InvalidSizeVariantException('size variant ' . $sizeVariantType . 'invalid'),
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
	 * @param int            $sizeVariantType the type of the desired size variant;
	 *                                        allowed values are:
	 *                                        {@link SizeVariant::ORIGINAL},
	 *                                        {@link SizeVariant::MEDIUM2X},
	 *                                        {@link SizeVariant::MEDIUM2},
	 *                                        {@link SizeVariant::SMALL2X},
	 *                                        {@link SizeVariant::SMALL},
	 *                                        {@link SizeVariant::THUMB2X}, and
	 *                                        {@link SizeVariant::THUMB}
	 * @param string         $shortPath       the short path of the media file this
	 *                                        size variant shall point to
	 * @param ImageDimension $dim             the width of the size variant
	 * @param int            $filesize        the filesize of the size variant
	 *
	 * @return SizeVariant The newly created and persisted size variant
	 *
	 * @throws IllegalOrderOfOperationException
	 * @throws ModelDBException
	 */
	public function create(int $sizeVariantType, string $shortPath, ImageDimension $dim, int $filesize): SizeVariant
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
			// thrown by ::add(), if  $result->photo_id != $this->photo->id,
			// but we know that we assert that
			assert(false, new \AssertionError('::add failed', $e));
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
		$ids = [];

		$ids[] = $this->original?->id;
		$this->original = null;
		$ids[] = $this->medium2x?->id;
		$this->medium2x = null;
		$ids[] = $this->medium?->id;
		$this->medium = null;
		$ids[] = $this->small2x?->id;
		$this->small2x = null;
		$ids[] = $this->small?->id;
		$this->small = null;
		$ids[] = $this->thumb2x?->id;
		$this->thumb2x = null;
		$ids[] = $this->thumb?->id;
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
		static::replicateSizeVariant($duplicate, $this->original);
		static::replicateSizeVariant($duplicate, $this->medium2x);
		static::replicateSizeVariant($duplicate, $this->medium);
		static::replicateSizeVariant($duplicate, $this->small2x);
		static::replicateSizeVariant($duplicate, $this->small);
		static::replicateSizeVariant($duplicate, $this->thumb2x);
		static::replicateSizeVariant($duplicate, $this->thumb);

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
}
