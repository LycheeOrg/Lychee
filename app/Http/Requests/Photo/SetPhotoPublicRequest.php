<?php

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\HasPhoto;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditPhotoTrait;
use App\Http\Requests\Traits\HasPhotoTrait;
use App\Http\RuleSets\Photo\SetPhotoPublicRuleSet;
use App\Models\Photo;

/**
 * Class SetPhotoPublicRequest.
 */
class SetPhotoPublicRequest extends BaseApiRequest implements HasPhoto
{
	use HasPhotoTrait;
	use AuthorizeCanEditPhotoTrait;

	protected bool $isPublic = false;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return SetPhotoPublicRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photo = Photo::query()->firstOrFail($values[RequestAttribute::PHOTO_ID_ATTRIBUTE]);
		$this->isPublic = static::toBoolean($values[RequestAttribute::IS_PUBLIC_ATTRIBUTE]);
	}

	public function isPublic(): bool
	{
		return $this->isPublic;
	}
}
