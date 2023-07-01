<?php

namespace App\Models\Extensions;

use App\DTO\AbstractDTO;
use App\DTO\SortingCriterion;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\OrderSortingType;
use App\Enum\SizeVariantType;
use App\Exceptions\InvalidPropertyException;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;

class Thumb extends AbstractDTO
{
	public string $id;
	public string $type;
	public ?string $thumbUrl;
	public ?string $thumb2xUrl;

	protected function __construct(string $id, string $type, string $thumbUrl, ?string $thumb2xUrl = null)
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
		return $relation->whereIn('type', [SizeVariantType::THUMB, SizeVariantType::THUMB2X]);
	}

	/**
	 * Creates a thumb by using the best rated photo from the given queryable.
	 *
	 * Note, this method assumes that the relation is already restricted
	 * such that it only returns photos which the current user may see.
	 *
	 * @param Relation|Builder $photoQueryable the relation to or query for {@link Photo} which is used to pick a thumb
	 * @param SortingCriterion $sorting        the sorting criterion
	 *
	 * @return Thumb|null the created thumbnail; null if the relation is empty
	 *
	 * @throws InvalidPropertyException thrown, if $sortingOrder neither
	 *                                  equals `desc` nor `asc`
	 */
	public static function createFromQueryable(Relation|Builder $photoQueryable, SortingCriterion $sorting): ?Thumb
	{
		try {
			/** @var Photo|null $cover */
			$cover = $photoQueryable
				->withOnly(['size_variants' => (fn (HasMany $r) => self::sizeVariantsFilter($r))])
				->orderBy('photos.' . ColumnSortingPhotoType::IS_STARRED->value, OrderSortingType::DESC->value)
				->orderBy('photos.' . $sorting->column->value, $sorting->order->value)
				->select(['photos.id', 'photos.type'])
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
		$thumb = $photo?->size_variants->getThumb();
		if ($thumb === null) {
			return null;
		}
		$thumb2x = $photo->size_variants->getThumb2x();

		return new self(
			$photo->id,
			$photo->type,
			$thumb->url,
			$thumb2x?->url
		);
	}

	/**
	 * Serializes this object into an array.
	 *
	 * @return array<string, string|null> The serialized properties of this object
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
}
