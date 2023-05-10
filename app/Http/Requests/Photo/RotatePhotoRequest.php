<?php

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\HasPhoto;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditPhotoTrait;
use App\Http\Requests\Traits\HasPhotoTrait;
use App\Http\RuleSets\Photo\RotatePhotoRuleSet;
use App\Models\Photo;

class RotatePhotoRequest extends BaseApiRequest implements HasPhoto
{
	use HasPhotoTrait;
	use AuthorizeCanEditPhotoTrait;

	protected int $direction;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return RotatePhotoRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var ?string $photoID */
		$photoID = $values[RequestAttribute::PHOTO_ID_ATTRIBUTE];
		$this->photo = Photo::query()
			->with(['size_variants'])
			->findOrFail($photoID);
		$this->direction = intval($values[RequestAttribute::DIRECTION_ATTRIBUTE]);
	}

	public function direction(): int
	{
		return $this->direction;
	}
}
