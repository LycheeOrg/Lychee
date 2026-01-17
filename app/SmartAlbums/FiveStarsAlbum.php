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
 * Smart album containing photos with perfect 5-star rating (rating_avg >= 5.0).
 */
class FiveStarsAlbum extends BaseSmartAlbum
{
	public const ID = SmartAlbumType::FIVE_STARS->value;

	/**
	 * @throws ConfigurationKeyMissingException
	 * @throws FrameworkException
	 */
	protected function __construct()
	{
		parent::__construct(
			id: SmartAlbumType::FIVE_STARS,
			smart_condition: fn (Builder $q) => $q->where('photos.rating_avg', '>=', 5.0)
		);
	}

	public static function getInstance(): self
	{
		return new self();
	}

	/**
	 * Override sorting: FiveStarsAlbum sorts by rating_avg DESC.
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
