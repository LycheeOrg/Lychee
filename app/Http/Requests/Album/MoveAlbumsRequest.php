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
		/** @var string|null $id */
		$id = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE];
		/** @var array<int,string> $ids */
		$ids = $values[RequestAttribute::ALBUM_IDS_ATTRIBUTE];
		$this->album = $id === null ?
			null :
			Album::findOrFail($id);
		/** @phpstan-ignore-next-line */
		$this->albums = Album::findOrFail($ids);
	}
}
