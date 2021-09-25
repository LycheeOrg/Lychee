<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumID;
use App\Http\Requests\Contracts\HasAlbumModelID;
use App\Http\Requests\Traits\HasAlbumModelIDTrait;
use App\Rules\ModelIDRule;

class SetAlbumNSFWRequest extends BaseApiRequest implements HasAlbumModelID
{
	use HasAlbumModelIDTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumWrite([$this->albumID]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAlbumID::ALBUM_ID_ATTRIBUTE => ['required', new ModelIDRule(false)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumID = intval($values[HasAlbumID::ALBUM_ID_ATTRIBUTE]);
	}
}
