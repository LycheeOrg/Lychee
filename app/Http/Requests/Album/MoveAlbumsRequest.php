<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumID;
use App\Http\Requests\Contracts\HasAlbumIDs;
use App\Http\Requests\Contracts\HasAlbumModelID;
use App\Http\Requests\Contracts\HasAlbumModelIDs;
use App\Http\Requests\Traits\HasAlbumIDsTrait;
use App\Http\Requests\Traits\HasAlbumModelIDTrait;
use App\Rules\ModelIDListRule;
use App\Rules\ModelIDRule;

class MoveAlbumsRequest extends BaseApiRequest implements HasAlbumModelID, HasAlbumModelIDs
{
	use HasAlbumModelIDTrait;
	use HasAlbumIDsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumWrite(array_merge([$this->albumID], $this->albumIDs));
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAlbumID::ALBUM_ID_ATTRIBUTE => ['required', new ModelIDRule(false)],
			HasAlbumIDs::ALBUM_IDS_ATTRIBUTE => ['required', new ModelIDListRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumID = intval($values[HasAlbumID::ALBUM_ID_ATTRIBUTE]);
		$this->albumIDs = explode(',', $values[HasAlbumIDs::ALBUM_IDS_ATTRIBUTE]);
		array_walk($this->albumIDs, function (&$id) { $id = intval($id); });
	}
}
