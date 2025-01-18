<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Settings;

use App\Enum\ColumnSortingAlbumType;
use App\Enum\ColumnSortingPhotoType;
use App\Enum\OrderSortingType;
use App\Http\Requests\BaseApiRequest;
use App\Models\Configs;
use App\Policies\SettingsPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;

final class SetSortingSettingsRequest extends BaseApiRequest
{
	public const PHOTO_SORTING_COLUMN_ATTRIBUTE = 'sorting_photos_column';
	public const PHOTO_SORTING_ORDER_ATTRIBUTE = 'sorting_photos_order';
	public const ALBUM_SORTING_COLUMN_ATTRIBUTE = 'sorting_albums_column';
	public const ALBUM_SORTING_ORDER_ATTRIBUTE = 'sorting_albums_order';

	protected ColumnSortingPhotoType $photoSortingColumn;
	protected OrderSortingType $photoSortingOrder;
	protected ColumnSortingAlbumType $albumSortingColumn;
	protected OrderSortingType $albumSortingOrder;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(SettingsPolicy::CAN_EDIT, Configs::class);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			self::PHOTO_SORTING_COLUMN_ATTRIBUTE => ['required', new Enum(ColumnSortingPhotoType::class)],
			self::PHOTO_SORTING_ORDER_ATTRIBUTE => ['required', new Enum(OrderSortingType::class)],
			self::ALBUM_SORTING_COLUMN_ATTRIBUTE => ['required', new Enum(ColumnSortingAlbumType::class)],
			self::ALBUM_SORTING_ORDER_ATTRIBUTE => ['required', new Enum(OrderSortingType::class)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photoSortingColumn = ColumnSortingPhotoType::from($values[self::PHOTO_SORTING_COLUMN_ATTRIBUTE]);
		$this->photoSortingOrder = OrderSortingType::from($values[self::PHOTO_SORTING_ORDER_ATTRIBUTE]);
		$this->albumSortingColumn = ColumnSortingAlbumType::from($values[self::ALBUM_SORTING_COLUMN_ATTRIBUTE]);
		$this->albumSortingOrder = OrderSortingType::from($values[self::ALBUM_SORTING_ORDER_ATTRIBUTE]);
	}

	/**
	 * @return ColumnSortingPhotoType
	 */
	public function photoSortingColumn(): ColumnSortingPhotoType
	{
		return $this->photoSortingColumn;
	}

	/**
	 * @return OrderSortingType
	 */
	public function photoSortingOrder(): OrderSortingType
	{
		return $this->photoSortingOrder;
	}

	/**
	 * @return ColumnSortingAlbumType
	 */
	public function albumSortingColumn(): ColumnSortingAlbumType
	{
		return $this->albumSortingColumn;
	}

	/**
	 * @return OrderSortingType
	 */
	public function albumSortingOrder(): OrderSortingType
	{
		return $this->albumSortingOrder;
	}
}
