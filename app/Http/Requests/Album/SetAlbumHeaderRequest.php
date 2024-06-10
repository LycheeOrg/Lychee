<?php

namespace App\Http\Requests\Album;

use App\Contracts\Http\Requests\HasAlbum;
use App\Contracts\Http\Requests\HasPhoto;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Contracts\Models\AbstractAlbum;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Http\Requests\Traits\HasPhotoTrait;
use App\Http\RuleSets\Album\SetAlbumHeaderRuleSet;
use App\Models\Album;
use App\Models\Photo;
use App\Policies\AlbumPolicy;
use App\Policies\PhotoPolicy;
use Illuminate\Support\Facades\Gate;

class SetAlbumHeaderRequest extends BaseApiRequest implements HasAlbum, HasPhoto
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
		return SetAlbumHeaderRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var string|null */
		$albumID = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE];

		$this->album = Album::query()->findOrFail($albumID);
		/** @var ?string $photoID */
		$photoID = $values[RequestAttribute::PHOTO_ID_ATTRIBUTE];
		$this->photo = $photoID === null ? null : Photo::query()->findOrFail($photoID);
	}
}
