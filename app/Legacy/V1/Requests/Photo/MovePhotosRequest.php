<?php

namespace App\Legacy\V1\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\RuleSets\Photo\MovePhotosRuleSet;
use App\Legacy\V1\Contracts\Http\Requests\HasAlbum;
use App\Legacy\V1\Contracts\Http\Requests\HasPhotos;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\Authorize\AuthorizeCanEditPhotosAlbumTrait;
use App\Legacy\V1\Requests\Traits\HasAlbumTrait;
use App\Legacy\V1\Requests\Traits\HasPhotosTrait;
use App\Models\Album;
use App\Models\Photo;

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
		return MovePhotosRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var array<int,string> $photosIDs */
		$photosIDs = $values[RequestAttribute::PHOTO_IDS_ATTRIBUTE];
		$this->photos = Photo::query()
			->findOrFail($photosIDs);
		/** @var string|null */
		$targetAlbumID = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE];
		$this->album = $targetAlbumID === null ? null : Album::query()->findOrFail($targetAlbumID);
	}
}
