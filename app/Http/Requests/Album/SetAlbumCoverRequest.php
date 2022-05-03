<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAbstractAlbum;
use App\Http\Requests\Contracts\HasAlbum;
use App\Http\Requests\Contracts\HasPhoto;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Http\Requests\Traits\HasPhotoTrait;
use App\Models\Album;
use App\Models\Photo;
use App\Rules\RandomIDRule;

class SetAlbumCoverRequest extends BaseApiRequest implements HasAlbum, HasPhoto
{
	use HasAlbumTrait;
	use HasPhotoTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumWrite($this->album) &&
			$this->authorizePhotoVisible($this->photo);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAbstractAlbum::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			HasPhoto::PHOTO_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = Album::query()->findOrFail($values[HasAbstractAlbum::ALBUM_ID_ATTRIBUTE]);
		$photoID = $values[HasPhoto::PHOTO_ID_ATTRIBUTE];
		$this->photo = empty($photoID) ?
			null :
			Photo::query()->findOrFail($values[HasPhoto::PHOTO_ID_ATTRIBUTE]);
	}
}
