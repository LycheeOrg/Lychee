<?php

namespace App\Http\Requests\Album;

use App\Contracts\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbums;
use App\Http\Requests\Traits\HasAlbumsTrait;
use App\Rules\AlbumIDRule;

/**
 * @implements HasAlbums<AbstractAlbum>
 */
class DeleteAlbumsRequest extends BaseApiRequest implements HasAlbums
{
	use HasAlbumsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumsWrite($this->albums);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAlbums::ALBUM_IDS_ATTRIBUTE => 'required|array|min:1',
			HasAlbums::ALBUM_IDS_ATTRIBUTE . '.*' => ['required', new AlbumIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		// We do not eagerly load all photos, size_variants and
		// size_variants.symlinks here, because if we delete an entire
		// subtree this might get huge and lead to an out-of-memory
		// exception.
		// We use lazy-loading of chunks of photos (combined with eager
		// loading of size variants and symlinks) in {@link Album::delete}.
		$this->albums = $this->albumFactory->findAbstractAlbumsOrFail(
			$values[HasAlbums::ALBUM_IDS_ATTRIBUTE], false
		);
	}
}
