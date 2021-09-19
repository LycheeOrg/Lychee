<?php

namespace App\Models\Extensions;

use App\Exceptions\InvalidPropertyException;
use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Relations\Relation;
use JsonSerializable;

class Thumb implements Arrayable, JsonSerializable
{
	protected int $id;
	protected string $type;
	protected ?string $thumbUrl;
	protected ?string $thumb2xUrl;

	protected function __construct(int $id, string $type, ?string $thumbUrl, ?string $thumb2xUrl = null)
	{
		$this->id = $id;
		$this->type = $type;
		$this->thumbUrl = $thumbUrl;
		$this->thumb2xUrl = $thumb2xUrl;
	}

	/**
	 * Creates a thumb by using the best rated photo from the given relation.
	 *
	 * Note, this method assumes that the relation is already restricted
	 * such that it only returns photos which the current user may see.
	 *
	 * @param Relation $photoRelation the relation to photos which might be used to pick a thumb
	 * @param string   $sortingCol    the name of the column which shall be used to sort
	 * @param string   $sortingOrder  the sorting order either 'ASC' or 'DESC'
	 *
	 * @return Thumb|null the created thumbnail; null if the relation is empty
	 *
	 * @throws InvalidPropertyException thrown, if $sortingOrder neither
	 *                                  equals `desc` nor `asc`
	 */
	public static function createFromPhotoRelation(Relation $photoRelation, string $sortingCol, string $sortingOrder): ?Thumb
	{
		try {
			/** @var Photo|null $cover */
			$cover = $photoRelation
				->without(['album'])
				->orderBy('is_starred', 'DESC')
				->orderBy($sortingCol, $sortingOrder)
				->orderBy('id', 'ASC')
				->select(['id', 'type'])
				->first();

			return self::createFromPhoto($cover);
		} catch (\InvalidArgumentException $e) {
			throw new InvalidPropertyException('Sorting order invalid', $e);
		}
	}

	/**
	 * Creates a thumbnail from the given photo.
	 *
	 * @param Photo|null $photo the photo
	 *
	 * @return Thumb|null the created thumbnail or null if null has been passed
	 */
	public static function createFromPhoto(?Photo $photo): ?Thumb
	{
		if (!$photo) {
			return null;
		}
		$thumb = $photo->size_variants->getSizeVariant(SizeVariant::THUMB);
		$thumb2x = $photo->size_variants->getSizeVariant(SizeVariant::THUMB2X);

		return new self(
			$photo->id,
			$photo->type,
			$thumb ? $thumb->url : null,
			$thumb2x ? $thumb2x->url : null
		);
	}

	/**
	 * Serializes this object into an array.
	 *
	 * @return array The serialized properties of this object
	 */
	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'type' => $this->type,
			'thumb' => $this->thumbUrl,
			'thumb2x' => $this->thumb2xUrl,
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
}
