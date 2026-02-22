<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models\Extensions;

use App\DTO\SortingCriterion;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\OrderSortingType;
use App\Enum\SizeVariantType;
use App\Exceptions\InvalidPropertyException;
use App\Models\Photo;
use App\Repositories\ConfigManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Relation;

class Thumb
{
	public string $id;
	public string $type;
	public ?string $thumbUrl;
	public ?string $thumb2xUrl;
	public ?string $placeholderUrl;

	protected function __construct(string $id, string $type, string $thumb_url, ?string $thumb2x_url = null, ?string $placeholder_url = null)
	{
		$this->id = $id;
		$this->type = $type;
		$this->thumbUrl = $thumb_url;
		$this->thumb2xUrl = $thumb2x_url;
		$this->placeholderUrl = $placeholder_url;
	}

	/**
	 * Restricts the given relation for size variants such that only the
	 * necessary variants for a thumbnail are selected.
	 *
	 * @param HasMany $relation
	 *
	 * @return HasMany<Photo, Model>
	 */
	public static function sizeVariantsFilter(HasMany $relation): HasMany
	{
		$sv_album_thumbs = [SizeVariantType::SMALL, SizeVariantType::SMALL2X, SizeVariantType::THUMB, SizeVariantType::THUMB2X, SizeVariantType::PLACEHOLDER];

		return $relation->whereIn('type', $sv_album_thumbs);
	}

	/**
	 * Creates a thumb by using the best rated photo from the given queryable.
	 *
	 * Note, this method assumes that the relation is already restricted
	 * such that it only returns photos which the current user may see.
	 *
	 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
	 * @template TResult
	 *
	 * @param Relation<Photo,TDeclaringModel,TResult>|Builder<Photo> $photo_queryable the relation to or query for {@link Photo} which is used to pick a thumb
	 * @param SortingCriterion                                       $sorting         the sorting criterion
	 *
	 * @return Thumb|null the created thumbnail; null if the relation is empty
	 *
	 * @throws InvalidPropertyException thrown, if $sortingOrder neither
	 *                                  equals `desc` nor `asc`
	 */
	public static function createFromQueryable(Relation|Builder $photo_queryable, SortingCriterion $sorting): ?Thumb
	{
		try {
			/** @var Photo|null $cover */
			$cover = $photo_queryable
				->withOnly(['size_variants' => (fn ($r) => self::sizeVariantsFilter($r))])
				->orderBy('photos.' . ColumnSortingPhotoType::IS_HIGHLIGHTED->value, OrderSortingType::DESC->value)
				->orderBy('photos.' . $sorting->column->toColumn(), $sorting->order->value)
				->select(['photos.id', 'photos.type'])
				->first();

			return self::createFromPhoto($cover);
			// @codeCoverageIgnoreStart
		} catch (\InvalidArgumentException $e) {
			throw new InvalidPropertyException('Sorting order invalid', $e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Creates a thumb by using the best rated photo from the given queryable.
	 * In other words, same as above but this time we pick a random image instead.
	 *
	 * Note, this method assumes that the relation is already restricted
	 * such that it only returns photos which the current user may see.
	 *
	 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
	 * @template TResult
	 *
	 * @param Relation<Photo,TDeclaringModel,TResult>|Builder<Photo> $photo_queryable the relation to or query for {@link Photo} which is used to pick a thumb
	 *
	 * @return Thumb|null the created thumbnail; null if the relation is empty
	 *
	 * @throws InvalidPropertyException thrown, if $sortingOrder neither
	 *                                  equals `desc` nor `asc`
	 *
	 * @codeCoverageIgnore We don't need to test that one.
	 * Note that the inRandomOrder maybe slower than fetching length + random int.
	 */
	public static function createFromRandomQueryable(Relation|Builder $photo_queryable): ?Thumb
	{
		try {
			/** @var Photo|null $cover */
			$cover = $photo_queryable
				->withOnly(['size_variants' => (fn ($r) => self::sizeVariantsFilter($r))])
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
	 *
	 * @param Photo|null $photo the photo
	 *
	 * @return Thumb|null the created thumbnail or null if null has been passed
	 */
	public static function createFromPhoto(?Photo $photo): ?Thumb
	{
		if ($photo === null) {
			// @codeCoverageIgnoreStart
			return null;
			// @codeCoverageIgnoreEnd
		}

		$thumb = $photo->size_variants->getSmall() ?? $photo->size_variants->getThumb();
		if ($thumb === null) {
			// @codeCoverageIgnoreStart
			return null;
			// @codeCoverageIgnoreEnd
		}

		$thumb2x = $photo->size_variants->getSmall() !== null
			? $photo->size_variants->getSmall2x()
			: $photo->size_variants->getThumb2x();

		$config_manager = app(ConfigManager::class);
		$placeholder = ($config_manager->getValueAsBool('low_quality_image_placeholder'))
			? $photo->size_variants->getPlaceholder()
			// @codeCoverageIgnoreStart
			: null;
		// @codeCoverageIgnoreEnd

		return new self(
			$photo->id,
			$photo->type,
			$thumb->url,
			$thumb2x?->url,
			$placeholder?->url,
		);
	}
}