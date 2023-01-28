<?php

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasAlbum;
use App\Contracts\Http\Requests\HasAlbums;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditAlbumAlbumsTrait;
use App\Http\Requests\Traits\HasAlbumsTrait;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Http\RuleSets\Album\MoveAlbumsRuleSet;
use App\Models\Album;

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
		return MoveAlbumsRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$targetAlbumID = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE];
		$this->album = $targetAlbumID === null ?
			null :
			Album::query()->findOrFail($targetAlbumID);
		// `findOrFail` returns a union type, but we know that it returns the
		// correct collection in this case
		// @phpstan-ignore-next-line
		$this->albums = Album::query()->findOrFail($values[RequestAttribute::ALBUM_IDS_ATTRIBUTE]);
	}
}
