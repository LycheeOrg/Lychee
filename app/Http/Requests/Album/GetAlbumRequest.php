<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumID;
use App\Http\Requests\Traits\HasAlbumIDTrait;
use App\Rules\AlbumIDRule;

class GetAlbumRequest extends BaseApiRequest implements HasAlbumID
{
	use HasAlbumIDTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumAccess($this->albumID);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAlbumID::ALBUM_ID_ATTRIBUTE => ['required', new AlbumIDRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumID = $values[HasAlbumID::ALBUM_ID_ATTRIBUTE];
		if (empty($this->albumID)) {
			$this->albumID = null;
		}
	}
}
