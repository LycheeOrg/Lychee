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
 * Smart album containing photos with no ratings (rating_avg IS NULL).
 */
class UnratedAlbum extends BaseSmartAlbum
{
	public const ID = SmartAlbumType::UNRATED->value;

	/**
	 * @throws ConfigurationKeyMissingException
	 * @throws FrameworkException
	 */
	protected function __construct()
	{
		parent::__construct(
			id: SmartAlbumType::UNRATED,
			smart_condition: fn (Builder $q) => $q->whereNull('photos.rating_avg')
		);
	}

	public static function getInstance(): self
	{
		return new self();
	}

	/**
	 * Override sorting: UnratedAlbum sorts by created_at DESC.
	 */
	protected function getPhotosAttribute(): \Illuminate\Pagination\LengthAwarePaginator
	{
		if ($this->photos !== null) {
			return $this->photos;
		}

		$sorting = new PhotoSortingCriterion(
			column: \App\Enum\ColumnSortingPhotoType::CREATED_AT->toColumnSortingType(),
			order: \App\Enum\OrderSortingType::DESC
		);

		$photos = (new \App\Models\Extensions\SortingDecorator($this->photos()))
			->orderPhotosBy($sorting->column, $sorting->order)
			->paginate($this->config_manager->getValueAsInt('photos_pagination_limit'));
		$this->photos = $photos;

		return $this->photos;
	}
}
