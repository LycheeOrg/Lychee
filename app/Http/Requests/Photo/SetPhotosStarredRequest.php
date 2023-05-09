<?php

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\HasPhotos;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditPhotosTrait;
use App\Http\Requests\Traits\HasPhotosTrait;
use App\Http\RuleSets\Photo\SetPhotosStarredRuleSet;
use App\Models\Photo;

/**
 * Class SetPhotosStarredRequest.
 */
class SetPhotosStarredRequest extends BaseApiRequest implements HasPhotos
{
	use HasPhotosTrait;
	use AuthorizeCanEditPhotosTrait;

	public const IS_STARRED_ATTRIBUTE = 'is_starred';

	protected bool $isStarred = false;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return SetPhotosStarredRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var array $photosIDs */
		$photosIDs = $values[RequestAttribute::PHOTO_IDS_ATTRIBUTE];
		$this->photos = Photo::query()->findOrFail($photosIDs);
		$this->isStarred = static::toBoolean($values[RequestAttribute::IS_STARRED_ATTRIBUTE]);
	}

	public function isStarred(): bool
	{
		return $this->isStarred;
	}
}
