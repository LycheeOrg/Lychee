<?php

namespace App\Legacy\V1\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Legacy\V1\Contracts\Http\Requests\HasAlbum;
use App\Legacy\V1\Contracts\Http\Requests\HasAlbums;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\Authorize\AuthorizeCanEditAlbumAlbumsTrait;
use App\Legacy\V1\Requests\Traits\HasAlbumsTrait;
use App\Legacy\V1\Requests\Traits\HasAlbumTrait;
use App\Legacy\V1\RuleSets\Album\MoveAlbumsRuleSet;
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
