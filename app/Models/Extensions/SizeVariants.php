<?php

namespace App\Models\Extensions;

use App\Models\Logs;
use App\Models\Photo;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\Storage;
use JsonSerializable;

/**
 * Class SizeVariants.
 *
 * The original size is not stored in this sub-array but on the root level of the JSON response
 * TODO: Maybe harmonize and put original variant into this array, too? This would also avoid an ugly if-branch in SymLink#override.
 */
class SizeVariants implements Arrayable, JsonSerializable
{
	/** @var Photo the parent object this object is tied to */
	private Photo $photo;
	/** @var SizeVariant The thumbnail variant */
	private SizeVariant $thumb;
	/** @var SizeVariant|null The larger version of the thumbnail variant */
	private ?SizeVariant $thumb2x;
	/** @var SizeVariant|null The small variant */
	private ?SizeVariant $small;
	/** @var SizeVariant|null The larger version of the smaller variant */
	private ?SizeVariant $small2x;
	/** @var SizeVariant|null The medium variant */
	private ?SizeVariant $medium;
	/** @var SizeVariant|null The larger version of the medium variant */
	private ?SizeVariant $medium2x;

	/**
	 * SizeVariants constructor.
	 *
	 * @param Photo $photo the parent object this object is tied to
	 */
	public function __construct(Photo $photo)
	{
		$this->photo = $photo;
		$this->thumb = SizeVariant::createSizeVariant($photo, SizeVariant::VARIANT_THUMB);
		$this->thumb2x = SizeVariant::createSizeVariant($photo, SizeVariant::VARIANT_THUMB2X);
		$this->small = SizeVariant::createSizeVariant($photo, SizeVariant::VARIANT_SMALL);
		$this->small2x = SizeVariant::createSizeVariant($photo, SizeVariant::VARIANT_SMALL2X);
		$this->medium = SizeVariant::createSizeVariant($photo, SizeVariant::VARIANT_MEDIUM);
		$this->medium2x = SizeVariant::createSizeVariant($photo, SizeVariant::VARIANT_MEDIUM2X);
	}

	/**
	 * Serializes this object into an array.
	 *
	 * @return array The serialized properties of this object
	 */
	public function toArray(): array
	{
		return [
			SizeVariant::VARIANT_THUMB => $this->thumb ? $this->thumb->toArray() : null,
			SizeVariant::VARIANT_THUMB2X => $this->thumb2x ? $this->thumb2x->toArray() : null,
			SizeVariant::VARIANT_SMALL => $this->small ? $this->small->toArray() : null,
			SizeVariant::VARIANT_SMALL2X => $this->small2x ? $this->small2x->toArray() : null,
			SizeVariant::VARIANT_MEDIUM => $this->medium ? $this->medium->toArray() : null,
			SizeVariant::VARIANT_MEDIUM2X => $this->medium2x ? $this->medium2x->toArray() : null,
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

	public function getThumb(): SizeVariant
	{
		return $this->thumb;
	}

	public function getThumb2x(): ?SizeVariant
	{
		return $this->thumb2x;
	}

	public function getSmall(): ?SizeVariant
	{
		return $this->small;
	}

	public function getSmall2x(): ?SizeVariant
	{
		return $this->small2x;
	}

	public function getMedium(): ?SizeVariant
	{
		return $this->medium;
	}

	public function getMedium2x(): ?SizeVariant
	{
		return $this->medium2x;
	}

	/**
	 * Deletes all size variants from storage.
	 *
	 * @return bool True on success, false otherwise
	 */
	public function deleteFromStorage(): bool
	{
		$success = true;
		if ($this->thumb) {
			$success &= $this->deleteFromStorageInternal($this->thumb->getRelativePath());
		}
		if ($this->thumb2x) {
			$success &= $this->deleteFromStorageInternal($this->thumb2x->getRelativePath());
		}
		if ($this->small) {
			$success &= $this->deleteFromStorageInternal($this->small->getRelativePath());
		}
		if ($this->small2x) {
			$success &= $this->deleteFromStorageInternal($this->small2x->getRelativePath());
		}
		if ($this->medium) {
			$success &= $this->deleteFromStorageInternal($this->medium->getRelativePath());
		}
		if ($this->medium2x) {
			$success &= $this->deleteFromStorageInternal($this->medium2x->getRelativePath());
		}

		return $success;
	}

	/**
	 * Deletes a size variant from storage given its relative path.
	 *
	 * @param string $relativePath
	 *
	 * @return bool True on success, false otherwise
	 */
	protected function deleteFromStorageInternal(string $relativePath): bool
	{
		if (!Storage::exists($relativePath)) {
			Logs::error(__METHOD__, __LINE__, 'Could not find file ' . $relativePath);

			return false;
		} elseif (!Storage::delete($relativePath)) {
			Logs::error(__METHOD__, __LINE__, 'Could not delete file ' . $relativePath);

			return false;
		}

		return true;
	}
}