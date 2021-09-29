<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumID;
use App\Http\Requests\Contracts\HasAlbumModelID;
use App\Http\Requests\Contracts\HasPhotoIDs;
use App\Http\Requests\Traits\HasAlbumModelIDTrait;
use App\Http\Requests\Traits\HasPhotoIDsTrait;
use App\Rules\ModelIDListRule;
use App\Rules\ModelIDRule;

class MovePhotosRequest extends BaseApiRequest implements HasPhotoIDs, HasAlbumModelID
{
	use HasPhotoIDsTrait;
	use HasAlbumModelIDTrait;

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
			HasPhotoIDs::PHOTO_IDS_ATTRIBUTE => ['required', new ModelIDListRule()],
			HasAlbumID::ALBUM_ID_ATTRIBUTE => ['present', new ModelIDRule(true)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photoIDs = explode(',', $values[HasPhotoIDs::PHOTO_IDS_ATTRIBUTE]);
		array_walk($this->photoIDs, function (&$id) { $id = intval($id); });
		$this->albumID = intval($values[HasAlbumID::ALBUM_ID_ATTRIBUTE]) ?? null;
	}
}
