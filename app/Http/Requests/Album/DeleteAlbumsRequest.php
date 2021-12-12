<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumIDs;
use App\Http\Requests\Traits\HasAlbumIDsTrait;
use App\Rules\AlbumIDListRule;

class DeleteAlbumsRequest extends BaseApiRequest implements HasAlbumIDs
{
	use HasAlbumIDsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumWrite($this->albumIDs);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAlbumIDs::ALBUM_IDS_ATTRIBUTE => ['required', new AlbumIDListRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumIDs = explode(',', $values[HasAlbumIDs::ALBUM_IDS_ATTRIBUTE]);
	}
}
