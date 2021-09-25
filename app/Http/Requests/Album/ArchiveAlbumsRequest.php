<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumIDs;
use App\Http\Requests\Contracts\HasAlbumModelIDs;
use App\Http\Requests\Traits\HasAlbumIDsTrait;
use App\Rules\ModelIDListRule;

class ArchiveAlbumsRequest extends BaseApiRequest implements HasAlbumModelIDs
{
	use HasAlbumIDsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		foreach ($this->albumIDs as $albumID) {
			if (!$this->authorizeAlbumAccess($albumID)) {
				return false;
			}
		}

		return true;
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAlbumIDs::ALBUM_IDS_ATTRIBUTE => ['required', new ModelIDListRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumIDs = explode(',', $values[HasAlbumIDs::ALBUM_IDS_ATTRIBUTE]);
		array_walk($this->albumIDs, function (&$id) { $id = intval($id); });
	}
}
