<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\SmartAlbums;

use App\DTO\PhotoSortingCriterion;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\OrderSortingType;
use App\Enum\SmartAlbumType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use App\Models\Extensions\SortingDecorator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Smart album containing photos with 2-star rating (2.0 <= rating_avg < 3.0).
 */
class TwoStarsAlbum extends BaseSmartAlbum
{
	public const ID = SmartAlbumType::TWO_STARS->value;

	/**
	 * @throws ConfigurationKeyMissingException
	 * @throws FrameworkException
	 */
	protected function __construct()
	{
		parent::__construct(
			id: SmartAlbumType::TWO_STARS,
			smart_condition: fn (Builder $q) => $q
				   ->where('photos.rating_avg', '>=', 2.0)
				   ->where('photos.rating_avg', '<', 3.0)
				   ->whereNotNull('photos.rating_avg')
		);
	}

	public static function getInstance(): self
	{
		return new self();
	}

	/**
	 * Override sorting: TwoStarsAlbum sorts by rating_avg DESC.
	 */
	protected function getPhotosAttribute(): LengthAwarePaginator
	{
		if ($this->photos !== null) {
			return $this->photos;
		}

		$sorting = new PhotoSortingCriterion(
			column: ColumnSortingPhotoType::RATING_AVG->toColumnSortingType(),
			order: OrderSortingType::DESC
		);

		$photos = (new SortingDecorator($this->photos()))
			->orderPhotosBy($sorting->column, $sorting->order)
			->paginate($this->config_manager->getValueAsInt('photos_pagination_limit'));
		$this->photos = $photos;

		return $this->photos;
	}
}
