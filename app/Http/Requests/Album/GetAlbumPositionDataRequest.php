<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAbstractAlbum;
use App\Http\Requests\Traits\HasAbstractAlbumTrait;
use App\Rules\AlbumIDRule;

class GetAlbumPositionDataRequest extends BaseApiRequest implements HasAbstractAlbum
{
	use HasAbstractAlbumTrait;

	public const INCLUDE_SUB_ALBUMS_ATTRIBUTE = 'includeSubAlbums';

	protected bool $includeSubAlbums = false;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumAccess($this->album);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAbstractAlbum::ALBUM_ID_ATTRIBUTE => ['required', new AlbumIDRule(false)],
			self::INCLUDE_SUB_ALBUMS_ATTRIBUTE => 'required|boolean',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		// Avoid loading all photos and sub-albums of an album, because
		// \App\Actions\Album\PositionData::get is only interested in a
		// particular subset of photos.
		$this->album = $this->albumFactory->findAbstractAlbumOrFail($values[HasAbstractAlbum::ALBUM_ID_ATTRIBUTE], false);
		$this->includeSubAlbums = static::toBoolean($values[self::INCLUDE_SUB_ALBUMS_ATTRIBUTE]);
	}

	public function includeSubAlbums(): bool
	{
		return $this->includeSubAlbums;
	}
}
