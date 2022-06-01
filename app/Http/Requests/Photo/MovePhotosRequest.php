<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAbstractAlbum;
use App\Http\Requests\Contracts\HasAlbum;
use App\Http\Requests\Contracts\HasPhotos;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Http\Requests\Traits\HasPhotosTrait;
use App\Models\Album;
use App\Models\Photo;
use App\Rules\RandomIDListRule;
use App\Rules\RandomIDRule;

class MovePhotosRequest extends BaseApiRequest implements HasPhotos, HasAlbum
{
	use HasPhotosTrait;
	use HasAlbumTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizePhotosWrite($this->photos) &&
			$this->authorizeAlbumWrite($this->album);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasPhotos::PHOTO_IDS_ATTRIBUTE => ['required', new RandomIDListRule()],
			HasPhotos::PHOTO_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
			HasAbstractAlbum::ALBUM_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photos = Photo::query()->findOrFail(
			explode(',', $values[HasPhotos::PHOTO_IDS_ATTRIBUTE])
		);
		$targetAlbumID = $values[HasAbstractAlbum::ALBUM_ID_ATTRIBUTE];
		$this->album = empty($targetAlbumID) ?
			null :
			Album::query()->findOrFail($targetAlbumID);
	}
}
