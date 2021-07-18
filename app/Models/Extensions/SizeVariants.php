<?php

namespace App\Models\Extensions;

use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * Class SizeVariants.
 */
class SizeVariants implements Arrayable, JsonSerializable
{
	const NAMES = [
		SizeVariant::THUMB => 'thumb',
		SizeVariant::THUMB2X => 'thumb2x',
		SizeVariant::SMALL => 'small',
		SizeVariant::SMALL2X => 'small2x',
		SizeVariant::MEDIUM => 'medium',
		SizeVariant::MEDIUM2X => 'medium2x',
		SizeVariant::ORIGINAL => 'original',
	];

	/** @var Photo the parent object this object is tied to */
	private Photo $photo;

	/**
	 * SizeVariants constructor.
	 *
	 * @param Photo $photo the parent object this object is tied to
	 */
	public function __construct(Photo $photo)
	{
		$this->photo = $photo;
		$this->namingStrategy = null;
	}

	/**
	 * Serializes this object into an array.
	 *
	 * @return array The serialized properties of this object
	 */
	public function toArray(): array
	{
		$result = [];
		/**
		 * @var int    $variant
		 * @var string $name
		 */
		foreach (self::NAMES as $variant => $name) {
			$sv = $this->getSizeVariant($variant);
			$result[$name] = $sv ? $sv->toArray() : null;
		}

		return $result;
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
		// Sic! Search on the whole Eloquent Collection not on the database
		// Please note, that `size_variant_raw` is called as an attribute
		// not as a method.
		// If it was called as a method, then the methods return type would
		// be an HasMany object and the query would be executed on the
		// database which is extremely inefficient, because it happens a lot
		// of times.
		// Moreover, calling `where` on the `HasMany`-relationship with
		// different values would not benefit from eager loading.
		// However, the number of size variants per photo is small (there are
		// at most seven).
		// So it is better to fetch all size variants once and then search
		// on the size variants.
		return $this->photo
			->size_variants_raw
			->where('size_variant', '=', $sizeVariant)
			->first();
	}

	/**
	 * Creates a new instance of {@link \App\Models\SizeVariant} for the
	 * associated photo and persist it to DB.
	 *
	 * @param int    $sizeVariant the type of the desired size variant
	 * @param string $shortPath   the short path of the media file this size variant shall point to
	 * @param int    $width       the width of the size variant
	 * @param int    $height      the height of the size variant
	 *
	 * @return SizeVariant The newly created and persisted size variant
	 */
	public function createSizeVariant(int $sizeVariant, string $shortPath, int $width, int $height): SizeVariant
	{
		if (!$this->photo->exists) {
			throw new \LogicException('cannot create a size variant for a photo whose id is not yet persisted to DB');
		}
		/** @var SizeVariant $result */
		$result = $this->photo->size_variants_raw()->make();
		$result->size_variant = $sizeVariant;
		$result->short_path = $shortPath;
		$result->width = $width;
		$result->height = $height;
		if (!$result->save()) {
			throw new \RuntimeException('could not persist size variant');
		}

		return $result;
	}

	/**
	 * Deletes all size variants incl. the files from storage.
	 *
	 * @param bool $keepOriginalFile if true, the original size variant is
	 *                               still removed from the DB and the model,
	 *                               but the media file is kept
	 * @param bool $keepAllFiles     if true, the all size variants are still
	 *                               removed from the DB and the model, but
	 *                               the media files are kept
	 *
	 * @return bool True on success, false otherwise
	 */
	public function delete(bool $keepOriginalFile = false, bool $keepAllFiles = false): bool
	{
		$success = true;
		foreach (self::NAMES as $variant => $name) {
			$sv = $this->getSizeVariant($variant);
			if ($sv) {
				$keepFile = (($variant === SizeVariant::ORIGINAL) && $keepOriginalFile) || $keepAllFiles;
				$success &= $sv->delete($keepFile);
			}
		}
		// ensure that relation `size_variants_raw` is refreshed and does not
		// contain size variant models which have been removed from DB.
		$this->photo->refresh();

		return $success;
	}
}
