<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumIDs;
use App\Http\Requests\Traits\HasAlbumIDsTrait;
use App\Rules\AlbumIDRule;

class DeleteAlbumsRequest extends BaseApiRequest implements HasAlbumIDs
{
	use HasAlbumIDsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumsWriteByIDs($this->albumIDs);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAlbumIDs::ALBUM_IDS_ATTRIBUTE => 'required|array|min:1',
			HasAlbumIDs::ALBUM_IDS_ATTRIBUTE . '.*' => ['required', new AlbumIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		// As we are going to delete the albums anyway, we don't load the
		// models for efficiency reasons.
		// Instead, we use mass deletion via low-level SQL queries later.
		$this->albumIDs = $values[HasAlbumIDs::ALBUM_IDS_ATTRIBUTE];
	}
}
