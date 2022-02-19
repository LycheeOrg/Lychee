<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAbstractAlbum;
use App\Http\Requests\Contracts\HasAlbum;
use App\Http\Requests\Contracts\HasAlbums;
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
	use HasAlbumsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumWrite($this->album) &&
			$this->authorizeAlbumsWrite($this->albums);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAbstractAlbum::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			HasAlbums::ALBUM_IDS_ATTRIBUTE => 'required|array|min:1',
			HasAlbums::ALBUM_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = Album::query()->findOrFail($values[HasAbstractAlbum::ALBUM_ID_ATTRIBUTE]);
		$this->albums = Album::query()
			->with(['children'])
			->findOrFail($values[HasAlbums::ALBUM_IDS_ATTRIBUTE]);
	}
}
