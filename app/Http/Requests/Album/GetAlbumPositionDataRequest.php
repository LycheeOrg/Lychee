<?php

namespace App\Http\Requests\Album;

use App\Factories\AlbumFactory;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbum;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Rules\AlbumIDRule;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class GetAlbumPositionDataRequest extends BaseApiRequest implements HasAlbum
{
	use HasAlbumTrait;

	public const INCLUDE_SUB_ALBUMS_ATTRIBUTE = 'includeSubAlbums';

	protected bool $includeSubAlbums = false;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumAccessByModel($this->album);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAlbum::ALBUM_ID_ATTRIBUTE => ['required', new AlbumIDRule()],
			self::INCLUDE_SUB_ALBUMS_ATTRIBUTE => 'required|boolean',
		];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws ModelNotFoundException
	 * @throws BindingResolutionException
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var AlbumFactory $albumFactory */
		$albumFactory = resolve(AlbumFactory::class);
		// Avoid loading all photos and sub-albums of an album, because
		// \App\Actions\Album\PositionData::get is only interested in a
		// particular subset of photos.
		$this->album = $albumFactory->findModelOrFail($values[HasAlbum::ALBUM_ID_ATTRIBUTE], false);
		$this->includeSubAlbums = static::toBoolean($values[self::INCLUDE_SUB_ALBUMS_ATTRIBUTE]);
	}

	public function includeSubAlbums(): bool
	{
		return $this->includeSubAlbums;
	}
}
