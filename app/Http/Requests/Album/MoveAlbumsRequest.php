<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAbstractAlbum;
use App\Http\Requests\Contracts\HasAlbum;
use App\Http\Requests\Contracts\HasAlbums;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditAlbumAlbumsTrait;
use App\Http\Requests\Traits\HasAlbumsTrait;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Models\Album;
use App\Rules\RandomIDRule;

/**
 * @implements HasAlbums<Album>
 */
class MoveAlbumsRequest extends BaseApiRequest implements HasAlbum, HasAlbums
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
			HasAbstractAlbum::ALBUM_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
			HasAlbums::ALBUM_IDS_ATTRIBUTE => 'required|array|min:1',
			HasAlbums::ALBUM_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$targetAlbumID = $values[HasAbstractAlbum::ALBUM_ID_ATTRIBUTE];
		$this->album = $targetAlbumID === null ?
			null :
			Album::query()->findOrFail($targetAlbumID);
		// `findOrFail` returns a union type, but we know that it returns the
		// correct collection in this case
		// @phpstan-ignore-next-line
		$this->albums = Album::query()->findOrFail($values[HasAlbums::ALBUM_IDS_ATTRIBUTE]);
	}
}
