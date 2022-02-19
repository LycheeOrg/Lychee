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
		$this->albums = $this->albumFactory->findAbstractAlbumsOrFail(
			$values[HasAlbums::ALBUM_IDS_ATTRIBUTE]
		);
	}
}
