<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumID;
use App\Http\Requests\Contracts\HasAlbumModelID;
use App\Http\Requests\Contracts\HasPhotoID;
use App\Http\Requests\Traits\HasAlbumModelIDTrait;
use App\Http\Requests\Traits\HasPhotoIDTrait;
use App\Rules\ModelIDRule;

class SetAlbumCoverRequest extends BaseApiRequest implements HasAlbumModelID, HasPhotoID
{
	use HasAlbumModelIDTrait;
	use HasPhotoIDTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumWrite([$this->albumID]) &&
			($this->photoID === null || $this->authorizePhotoVisible($this->photoID));
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAlbumID::ALBUM_ID_ATTRIBUTE => ['required', new ModelIDRule(false)],
			HasPhotoID::PHOTO_ID_ATTRIBUTE => ['present', new ModelIDRule(true)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumID = intval($values[HasAlbumID::ALBUM_ID_ATTRIBUTE]);
		$this->photoID = intval($values[HasPhotoID::PHOTO_ID_ATTRIBUTE]) ?? null;
	}
}
