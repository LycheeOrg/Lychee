<?php

namespace App\Http\Requests\Album;

use App\Contracts\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbums;
use App\Http\Requests\Traits\HasAlbumsTrait;
use App\Rules\AlbumIDListRule;

/**
 * @implements HasAlbums<AbstractAlbum>
 */
class ArchiveAlbumsRequest extends BaseApiRequest implements HasAlbums
{
	use HasAlbumsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumsAccess($this->albums);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAlbums::ALBUM_IDS_ATTRIBUTE => ['required', new AlbumIDListRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		// TODO: `App\Actions\Album\Archive::compressAlbum` iterates over the original size variant of each photo in the album; we should eagerly load them for higher efficiency.
		$this->albums = $this->albumFactory->findAbstractAlbumsOrFail(
			explode(',', $values[HasAlbums::ALBUM_IDS_ATTRIBUTE])
		);
	}
}
