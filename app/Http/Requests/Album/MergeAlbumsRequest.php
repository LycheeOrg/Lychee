<?php

declare(strict_types=1);

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasAlbum;
use App\Contracts\Http\Requests\HasAlbums;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditAlbumAlbumsTrait;
use App\Http\Requests\Traits\HasAlbumsTrait;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Http\RuleSets\Album\MergeAlbumsRuleSet;
use App\Models\Album;

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
		return MergeAlbumsRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var string $id */
		$id = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE];
		/** @var array<int,string> $ids */
		$ids = $values[RequestAttribute::ALBUM_IDS_ATTRIBUTE];
		$this->album = Album::query()->findOrFail($id);
		// @phpstan-ignore-next-line
		$this->albums = Album::query()
			->with(['children'])
			->findOrFail($ids);
	}
}
