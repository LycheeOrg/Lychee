<?php

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasAlbum;
use App\Contracts\Http\Requests\HasAlbums;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditAlbumAlbumsTrait;
use App\Http\Requests\Traits\HasAlbumsTrait;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Models\Album;
use App\Rules\RandomIDRule;

/**
 * @implements HasAlbums<Album>
 */
class MergeAlbumsRequest extends BaseApiRequest implements HasAlbum, HasAlbums
{
	use HasAlbumTrait;
	/** @phpstan-use HasAlbumsTrait<Album> */
	use HasAlbumsTrait;
	use AuthorizeCanEditAlbumAlbumsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::ALBUM_IDS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::ALBUM_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = Album::query()->findOrFail($values[RequestAttribute::ALBUM_ID_ATTRIBUTE]);
		// `findOrFail` returns a union type, but we know that it returns the
		// correct collection in this case
		// TODO: As part of our `FixedQueryBuilder` we should also consider adding a method `findManyOrFail` which does not return an union type, but only a `Collection`.
		// This would avoid using phpstan-ignore-next-line here and in many similar cases.
		// @phpstan-ignore-next-line
		$this->albums = Album::query()
			->with(['children'])
			->findOrFail($values[RequestAttribute::ALBUM_IDS_ATTRIBUTE]);
	}
}
