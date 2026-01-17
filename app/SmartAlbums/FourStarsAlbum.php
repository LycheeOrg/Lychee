<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\SmartAlbums;

use App\DTO\PhotoSortingCriterion;
use App\Enum\SmartAlbumType;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\FrameworkException;
use Illuminate\Database\Eloquent\Builder;

/**
 * Smart album containing photos with 4+ star rating (rating_avg >= 4.0).
 * This is a threshold album, including 4 and 5-star photos.
 */
class FourStarsAlbum extends BaseSmartAlbum
{
	public const ID = SmartAlbumType::FOUR_STARS->value;

	/**
	 * @throws ConfigurationKeyMissingException
	 * @throws FrameworkException
	 */
	protected function __construct()
	{
		parent::__construct(
			id: SmartAlbumType::FOUR_STARS,
			smart_condition: fn (Builder $q) => $q->where('photos.rating_avg', '>=', 4.0)
		);
	}

	public static function getInstance(): self
	{
		return new self();
	}

	/**
	 * Override sorting: FourStarsAlbum sorts by rating_avg DESC.
	 */
	protected function getPhotosAttribute(): \Illuminate\Pagination\LengthAwarePaginator
	{
		if ($this->photos !== null) {
			return $this->photos;
		}

		$sorting = new PhotoSortingCriterion(
			column: \App\Enum\ColumnSortingPhotoType::RATING_AVG->toColumnSortingType(),
			order: \App\Enum\OrderSortingType::DESC
		);

		$photos = (new \App\Models\Extensions\SortingDecorator($this->photos()))
			->orderPhotosBy($sorting->column, $sorting->order)
			->paginate($this->config_manager->getValueAsInt('photos_pagination_limit'));
		$this->photos = $photos;

		return $this->photos;
	}
}
