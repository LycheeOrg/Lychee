<?php

declare(strict_types=1);

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\HasAlbum;
use App\Contracts\Http\Requests\HasPhotos;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditPhotosAlbumTrait;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Http\Requests\Traits\HasPhotosTrait;
use App\Http\RuleSets\Photo\DuplicatePhotosRuleSet;
use App\Models\Album;
use App\Models\Photo;

class DuplicatePhotosRequest extends BaseApiRequest implements HasPhotos, HasAlbum
{
	use HasPhotosTrait;
	use HasAlbumTrait;
	use AuthorizeCanEditPhotosAlbumTrait;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return DuplicatePhotosRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var array<int,string> $photosIDs */
		$photosIDs = $values[RequestAttribute::PHOTO_IDS_ATTRIBUTE];
		$this->photos = Photo::query()
			->with(['size_variants'])
			->findOrFail($photosIDs);
		/** @var string|null */
		$targetAlbumID = $values[RequestAttribute::ALBUM_ID_ATTRIBUTE];
		$this->album = $targetAlbumID === null ?
			null :
			Album::query()->findOrFail($targetAlbumID);
	}
}
