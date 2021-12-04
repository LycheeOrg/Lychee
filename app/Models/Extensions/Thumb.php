<?php

namespace App\Models\Extensions;

use App\Models\Photo;
use App\Models\SizeVariant;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
	 * Restricts the given relation for size variants such that only the
	 * necessary variants for a thumbnail are selected.
	 *
	 * @param HasMany $relation
	 *
	 * @return HasMany
	 */
	public static function sizeVariantsFilter(HasMany $relation): HasMany
	{
		return $relation->whereIn('type', [SizeVariant::THUMB, SizeVariant::THUMB2X]);
	}

	/**
	 * Creates a thumb by using the best rated photo from the given queryable.
	 *
	 * Note, this method assumes that the relation is already restricted
	 * such that it only returns photos which the current user may see.
	 *
	 * @param Relation|Builder $photoQueryable the relation to or query for {@link Photo} which is used to pick a thumb
	 * @param string           $sortingCol     the name of the column which shall be used to sort
	 * @param string           $sortingOrder   the sorting order either 'ASC' or 'DESC'
	 *
	 * @return Thumb|null the created thumbnail; null if the relation is empty
	 */
	public static function createFromQueryable(Relation|Builder $photoQueryable, string $sortingCol, string $sortingOrder): ?Thumb
	{
		/** @var Photo|null $cover */
		$cover = $photoQueryable
			->withOnly(['size_variants' => fn (HasMany $r) => self::sizeVariantsFilter($r)])
			->orderBy('photos.is_starred', 'DESC')
			->orderBy('photos.' . $sortingCol, $sortingOrder)
			->select(['photos.id', 'photos.type'])
			->first();

		return self::createFromPhoto($cover);
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
		$thumb = $photo->size_variants->getThumb();
		$thumb2x = $photo->size_variants->getThumb2x();

		return new self(
			$photo->id,
			$photo->type,
			$thumb?->url,
			$thumb2x?->url
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
