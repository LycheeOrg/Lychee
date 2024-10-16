<?php

namespace App\Legacy\V1\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Legacy\V1\Contracts\Http\Requests\HasPhoto;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\Authorize\AuthorizeCanEditPhotoTrait;
use App\Legacy\V1\Requests\Traits\HasPhotoTrait;
use App\Legacy\V1\RuleSets\Photo\RotatePhotoRuleSet;
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
