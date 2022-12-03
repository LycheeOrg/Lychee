<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbum;
use App\Http\Requests\Contracts\HasPhotos;
use App\Http\Requests\Contracts\RequestAttribute;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditPhotosAlbumTrait;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Http\Requests\Traits\HasPhotosTrait;
use App\Models\Album;
use App\Models\Photo;
use App\Rules\RandomIDRule;

class MovePhotosRequest extends BaseApiRequest implements HasPhotos, HasAlbum
{
	use HasPhotosTrait;
	use HasAlbumTrait;
	use AuthorizeCanEditPhotosAlbumTrait;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::PHOTO_IDS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::PHOTO_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['present', new RandomIDRule(true)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photos = Photo::query()->findOrFail(
			$values[RequestAttribute::PHOTO_IDS_ATTRIBUTE]
		);
		$targetAlbumID = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE];
		$this->album = $targetAlbumID === null ?
			null :
			Album::query()->findOrFail($targetAlbumID);
	}
}
