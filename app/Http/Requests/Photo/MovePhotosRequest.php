<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumID;
use App\Http\Requests\Contracts\HasPhotoIDs;
use App\Http\Requests\Traits\HasAlbumIDTrait;
use App\Http\Requests\Traits\HasPhotoIDsTrait;
use App\Rules\RandomIDRule;

class MovePhotosRequest extends BaseApiRequest implements HasPhotoIDs, HasAlbumID
{
	use HasPhotoIDsTrait;
	use HasAlbumIDTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizePhotoWrite($this->photoIDs) &&
			$this->authorizeAlbumWrite([$this->albumID]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasPhotoIDs::PHOTO_IDS_ATTRIBUTE => 'required|array|min:1',
			HasPhotoIDs::PHOTO_IDS_ATTRIBUTE . '*' => ['required', new RandomIDRule(false)],
			HasAlbumID::ALBUM_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photoIDs = $values[HasPhotoIDs::PHOTO_IDS_ATTRIBUTE];
		$this->albumID = $values[HasAlbumID::ALBUM_ID_ATTRIBUTE] ?? null;
	}
}
