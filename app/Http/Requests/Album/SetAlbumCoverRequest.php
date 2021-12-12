<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumID;
use App\Http\Requests\Contracts\HasPhotoID;
use App\Http\Requests\Traits\HasAlbumIDTrait;
use App\Http\Requests\Traits\HasPhotoIDTrait;
use App\Rules\RandomIDRule;

class SetAlbumCoverRequest extends BaseApiRequest implements HasAlbumID, HasPhotoID
{
	use HasAlbumIDTrait;
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
			HasAlbumID::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			HasPhotoID::PHOTO_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumID = $values[HasAlbumID::ALBUM_ID_ATTRIBUTE];
		$this->photoID = $values[HasPhotoID::PHOTO_ID_ATTRIBUTE];
	}
}
