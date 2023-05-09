<?php

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\HasPhotos;
use App\Contracts\Http\Requests\HasTags;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditPhotosTrait;
use App\Http\Requests\Traits\HasPhotosTrait;
use App\Http\Requests\Traits\HasTagsTrait;
use App\Http\RuleSets\Photo\SetPhotosTagsRuleSet;
use App\Models\Photo;

class SetPhotosTagsRequest extends BaseApiRequest implements HasPhotos, HasTags
{
	use HasPhotosTrait;
	use HasTagsTrait;
	use AuthorizeCanEditPhotosTrait;

	public bool $shallOverride;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return SetPhotosTagsRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var array $photosIDs */
		$photosIDs = $values[RequestAttribute::PHOTO_IDS_ATTRIBUTE];
		$this->photos = Photo::query()->findOrFail($photosIDs);
		$this->tags = $values[RequestAttribute::TAGS_ATTRIBUTE];
		$this->shallOverride = $values[RequestAttribute::SHALL_OVERRIDE_ATTRIBUTE];
	}
}