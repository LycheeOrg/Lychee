<?php

namespace App\Http\Requests\Album;

use App\Contracts\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAbstractAlbum;
use App\Http\Requests\Contracts\HasAlbum;
use App\Http\Requests\Contracts\HasPhoto;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Http\Requests\Traits\HasPhotoTrait;
use App\Models\Album;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;

class SetAlbumCoverRequest extends BaseApiRequest implements HasAlbum, HasPhoto
{
	use HasAlbumTrait;
	use HasPhotoTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(AlbumPolicy::CAN_EDIT, [AbstractAlbum::class, $this->album]) &&
			($this->photo === null || Gate::check(PhotoPolicy::CAN_SEE, $this->photo));
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
		$this->photo = $photoID === null ?
			null :
			Photo::query()->findOrFail($values[HasPhoto::PHOTO_ID_ATTRIBUTE]);
	}
}
