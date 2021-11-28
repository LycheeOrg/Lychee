<?php

namespace App\Models\Extensions;

use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Collection;
use JsonSerializable;

/**
 * Class SizeVariants.
 */
class SizeVariants implements Arrayable, JsonSerializable
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
	 */
	public function __construct(Photo $photo, ?Collection $sizeVariants = null)
	{
		$this->photo = $photo;
		$this->namingStrategy = null;
		if ($sizeVariants) {
			/** @var SizeVariant $sizeVariant */
			foreach ($sizeVariants as $sizeVariant) {
				$this->add($sizeVariant);
			}
		}
	}

	public function add(SizeVariant $sizeVariant): void
	{
		if ($sizeVariant->photo_id !== $this->photo->id) {
			throw new \UnexpectedValueException('ID of owning photo does not match');
		}
		$sizeVariant->setRelation('photo', $this->photo);

		switch ($sizeVariant->size_variant) {
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
				throw new \UnexpectedValueException('size variant ' . $sizeVariant . 'invalid');
		}

		if ($ref && $ref->id !== $sizeVariant->id) {
			throw new \UnexpectedValueException('Another size variant of the same type has already been added');
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
			'original' => $this->original->toArray(),
			'medium2x' => $this->medium2x ? $this->medium2x->toArray() : null,
			'medium' => $this->medium ? $this->medium->toArray() : null,
			'small2x' => $this->small2x ? $this->small2x->toArray() : null,
			'small' => $this->small ? $this->small->toArray() : null,
			'thumb2x' => $this->thumb2x ? $this->thumb2x->toArray() : null,
			'thumb' => $this->thumb->toArray(),
		];
	}

	/**
	 * Serializes this object into an array.
	 *
	 * @return array The serialized properties of this object
	 *
	 * @see SizeVariants::toArray()
	 */
	public function jsonSerialize(): array
	{
		return $this->toArray();
	}

	/**
	 * Returns the requested size variant of the photo.
	 *
	 * @param int $sizeVariant The type of the size variant
	 *
	 * @return SizeVariant|null The size variant
	 */
	public function getSizeVariant(int $sizeVariant): ?SizeVariant
	{
		switch ($sizeVariant) {
			case SizeVariant::ORIGINAL:
				return $this->original;
			case SizeVariant::MEDIUM2X:
				return $this->medium2x;
			case SizeVariant::MEDIUM:
				return $this->medium;
			case SizeVariant::SMALL2X:
				return $this->small2x;
			case SizeVariant::SMALL:
				return $this->small;
			case SizeVariant::THUMB2X:
				return $this->thumb2x;
			case SizeVariant::THUMB:
				return $this->thumb;
			default:
				throw new \UnexpectedValueException('size variant ' . $sizeVariant . 'invalid');
		}
	}

	public function getOriginal(): ?SizeVariant
	{
		return $this->original;
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
	 * @param int    $sizeVariant the type of the desired size variant
	 * @param string $shortPath   the short path of the media file this size variant shall point to
	 * @param int    $width       the width of the size variant
	 * @param int    $height      the height of the size variant
	 *
	 * @return SizeVariant The newly created and persisted size variant
	 */
	public function create(int $sizeVariant, string $shortPath, int $width, int $height): SizeVariant
	{
		if (!$this->photo->exists) {
			throw new \LogicException('cannot create a size variant for a photo whose id is not yet persisted to DB');
		}
		/** @var SizeVariant $result */
		$result = new SizeVariant();
		$result->photo_id = $this->photo->id;
		$result->size_variant = $sizeVariant;
		$result->short_path = $shortPath;
		$result->width = $width;
		$result->height = $height;
		if (!$result->save()) {
			throw new \RuntimeException('could not persist size variant');
		}
		$this->add($result);

		return $result;
	}

	/**
	 * Deletes all size variants incl. the files from storage.
	 *
	 * @param bool $keepOriginalFile if true, the original size variant is
	 *                               still removed from the DB and the model,
	 *                               but the media file is kept
	 * @param bool $keepAllFiles     if true, all size variants are still
	 *                               removed from the DB and the model, but
	 *                               the media files are kept
	 *
	 * @return bool True on success, false otherwise
	 */
	public function deleteAll(bool $keepOriginalFile = false, bool $keepAllFiles = false): bool
	{
		$success = true;
		$success &= $this->original->delete($keepOriginalFile || $keepAllFiles);
		$this->original = null;
		$success &= !$this->medium2x || $this->medium2x->delete($keepAllFiles);
		$this->medium2x = null;
		$success &= !$this->medium || $this->medium->delete($keepAllFiles);
		$this->medium = null;
		$success &= !$this->small2x || $this->small2x->delete($keepAllFiles);
		$this->small2x = null;
		$success &= !$this->small || $this->small->delete($keepAllFiles);
		$this->small = null;
		$success &= !$this->thumb2x || $this->thumb2x->delete($keepAllFiles);
		$this->thumb2x = null;
		$success &= $this->thumb->delete($keepAllFiles);
		$this->thumb = null;

		return $success;
	}

	public function replicate(Photo $duplicatePhoto): SizeVariants
	{
		$duplicate = new SizeVariants($duplicatePhoto);
		$duplicate->namingStrategy = $this->namingStrategy;
		$this->replicateSizeVariant($duplicate, $this->original);
		$this->replicateSizeVariant($duplicate, $this->medium2x);
		$this->replicateSizeVariant($duplicate, $this->medium);
		$this->replicateSizeVariant($duplicate, $this->small2x);
		$this->replicateSizeVariant($duplicate, $this->small);
		$this->replicateSizeVariant($duplicate, $this->thumb2x);
		$this->replicateSizeVariant($duplicate, $this->thumb);

		return $duplicate;
	}

	private static function replicateSizeVariant(SizeVariants $duplicate, ?SizeVariant $sizeVariant): void
	{
		if ($sizeVariant !== null) {
			$duplicate->create($sizeVariant->size_variant, $sizeVariant->short_path, $sizeVariant->width, $sizeVariant->height);
		}
	}
}
