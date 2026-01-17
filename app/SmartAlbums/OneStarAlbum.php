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
 * Smart album containing photos with 1-star rating (1.0 <= rating_avg < 2.0).
 */
class OneStarAlbum extends BaseSmartAlbum
{
	public const ID = SmartAlbumType::ONE_STAR->value;

	/**
	 * @throws ConfigurationKeyMissingException
	 * @throws FrameworkException
	 */
	protected function __construct()
	{
		parent::__construct(
			id: SmartAlbumType::ONE_STAR,
			smart_condition: fn (Builder $q) => $q
				   ->where('photos.rating_avg', '>=', 1.0)
				   ->where('photos.rating_avg', '<', 2.0)
				   ->whereNotNull('photos.rating_avg')
		);
	}

	public static function getInstance(): self
	{
		return new self();
	}

	/**
	 * Override sorting: OneStarAlbum sorts by rating_avg DESC.
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
