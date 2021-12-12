<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumID;
use App\Http\Requests\Traits\HasAlbumIDTrait;
use App\Rules\AlbumIDRule;

class GetAlbumPositionDataRequest extends BaseApiRequest implements HasAlbumID
{
	use HasAlbumIDTrait;

	public const INCLUDE_SUB_ALBUMS_ATTRIBUTE = 'includeSubAlbums';

	protected bool $includeSubAlbums = false;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumAccess($this->albumID);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAlbumID::ALBUM_ID_ATTRIBUTE => ['required', new AlbumIDRule()],
			self::INCLUDE_SUB_ALBUMS_ATTRIBUTE => 'required|boolean',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumID = $values[HasAlbumID::ALBUM_ID_ATTRIBUTE];
		$this->includeSubAlbums = static::toBoolean($values[self::INCLUDE_SUB_ALBUMS_ATTRIBUTE]);
	}

	public function includeSubAlbums(): bool
	{
		return $this->includeSubAlbums;
	}
}
