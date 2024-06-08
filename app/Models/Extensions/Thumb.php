<?php

namespace App\Models\Extensions;

use App\Assets\Features;
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

/**
 * @extends AbstractDTO<string|null>
 */
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
	 * @param HasMany<Photo> $relation
	 *
	 * @return HasMany<Photo>
	 */
	public static function sizeVariantsFilter(HasMany $relation): HasMany
	{
		$svAlbumThumbs = [SizeVariantType::THUMB, SizeVariantType::THUMB2X];
		if (Features::active('livewire')) {
			$svAlbumThumbs = [SizeVariantType::SMALL, SizeVariantType::SMALL2X, SizeVariantType::THUMB, SizeVariantType::THUMB2X];
		}

		return $relation->whereIn('type', $svAlbumThumbs);
	}

	/**
	 * Creates a thumb by using the best rated photo from the given queryable.
	 *
	 * Note, this method assumes that the relation is already restricted
	 * such that it only returns photos which the current user may see.
	 *
	 * @param Relation<Photo>|Builder<Photo> $photoQueryable the relation to or query for {@link Photo} which is used to pick a thumb
	 * @param SortingCriterion               $sorting        the sorting criterion
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
	 * Creates a thumb by using the best rated photo from the given queryable.
	 * In other words, same as above but this time we pick a random image instead.
	 *
	 * Note, this method assumes that the relation is already restricted
	 * such that it only returns photos which the current user may see.
	 *
	 * @param Relation<Photo>|Builder<Photo> $photoQueryable the relation to or query for {@link Photo} which is used to pick a thumb
	 *
	 * @return Thumb|null the created thumbnail; null if the relation is empty
	 *
	 * @throws InvalidPropertyException thrown, if $sortingOrder neither
	 *                                  equals `desc` nor `asc`
	 */
	public static function createFromRandomQueryable(Relation|Builder $photoQueryable): ?Thumb
	{
		try {
			/** @var Photo|null $cover */
			$cover = $photoQueryable
				->withOnly(['size_variants' => (fn (HasMany $r) => self::sizeVariantsFilter($r))])
				->inRandomOrder()
				->select(['photos.id', 'photos.type'])
				->first();

			return self::createFromPhoto($cover);
		} catch (\InvalidArgumentException $e) {
			throw new InvalidPropertyException('Sorting order invalid', $e);
		}
	}

	/**
	 * Creates a thumbnail from the given photo.
	 * On Livewire it will use by default small and small2x if available, thumb and thumb2x if not.
	 * On Legacy it will use thumb and thumb2x.
	 *
	 * @param Photo|null $photo the photo
	 *
	 * @return Thumb|null the created thumbnail or null if null has been passed
	 */
	public static function createFromPhoto(?Photo $photo): ?Thumb
	{
		$thumb = (Features::active('livewire') && $photo?->size_variants->getSmall() !== null)
			? $photo->size_variants->getSmall()
			: $photo?->size_variants->getThumb();
		if ($thumb === null) {
			return null;
		}

		$thumb2x = (Features::active('livewire') && $photo?->size_variants->getSmall() !== null)
			? $photo->size_variants->getSmall2x()
			: $photo?->size_variants->getThumb2x();

		/**
		 * TODO: Code for later when Livewire is the only front-end.
		 */
		// $thumb = $photo?->size_variants->getSmall() ?? $photo?->size_variants->getThumb();
		// if ($thumb === null) {
		// 	return null;
		// }

		// $thumb2x = $photo?->size_variants->getSmall() !== null
		// 	? $photo?->size_variants->getSmall2x()
		// 	: $photo?->size_variants->getThumb2x();

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
	 * @return array<string,string|null> The serialized properties of this object
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
