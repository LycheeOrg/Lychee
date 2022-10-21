<?php

namespace App\Http\Requests\Settings;

use App\Rules\AlbumSortingRule;
use App\Rules\OrderRule;
use App\Rules\PhotoSortingRule;

class SetSortingRequest extends SettingRequest
{
	public const PHOTO_SORTING_COLUMN_ATTRIBUTE = 'sorting_photos_column';
	public const PHOTO_SORTING_ORDER_ATTRIBUTE = 'sorting_photos_order';
	public const ALBUM_SORTING_COLUMN_ATTRIBUTE = 'sorting_albums_column';
	public const ALBUM_SORTING_ORDER_ATTRIBUTE = 'sorting_albums_order';

	protected string $photoSortingColumn;
	protected string $photoSortingOrder;
	protected string $albumSortingColumn;
	protected string $albumSortingOrder;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			self::PHOTO_SORTING_COLUMN_ATTRIBUTE => ['required', new PhotoSortingRule()],
			self::PHOTO_SORTING_ORDER_ATTRIBUTE => ['required', new OrderRule(false)],
			self::ALBUM_SORTING_COLUMN_ATTRIBUTE => ['required', new AlbumSortingRule()],
			self::ALBUM_SORTING_ORDER_ATTRIBUTE => ['required', new OrderRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photoSortingColumn = $values[self::PHOTO_SORTING_COLUMN_ATTRIBUTE];
		$this->photoSortingOrder = $values[self::PHOTO_SORTING_ORDER_ATTRIBUTE];
		$this->albumSortingColumn = $values[self::ALBUM_SORTING_COLUMN_ATTRIBUTE];
		$this->albumSortingOrder = $values[self::ALBUM_SORTING_ORDER_ATTRIBUTE];
	}

	/**
	 * @return string
	 */
	public function photoSortingColumn(): string
	{
		return $this->photoSortingColumn;
	}

	/**
	 * @return string
	 */
	public function photoSortingOrder(): string
	{
		return $this->photoSortingOrder;
	}

	/**
	 * @return string
	 */
	public function albumSortingColumn(): string
	{
		return $this->albumSortingColumn;
	}

	/**
	 * @return string
	 */
	public function albumSortingOrder(): string
	{
		return $this->albumSortingOrder;
	}
}
