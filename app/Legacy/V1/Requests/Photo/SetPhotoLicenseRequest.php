<?php

namespace App\Legacy\V1\Requests\Photo;

use App\Enum\LicenseType;
use App\Http\Requests\BaseApiRequest;
use App\Http\RuleSets\Photo\SetPhotoLicenseRuleSet;
use App\Legacy\V1\Contracts\Http\Requests\HasLicense;
use App\Legacy\V1\Contracts\Http\Requests\HasPhoto;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\Authorize\AuthorizeCanEditPhotoTrait;
use App\Legacy\V1\Requests\Traits\HasLicenseTrait;
use App\Legacy\V1\Requests\Traits\HasPhotoTrait;
use App\Models\Photo;

class SetPhotoLicenseRequest extends BaseApiRequest implements HasPhoto, HasLicense
{
	use HasPhotoTrait;
	use HasLicenseTrait;
	use AuthorizeCanEditPhotoTrait;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return SetPhotoLicenseRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var ?string $photoID */
		$photoID = $values[RequestAttribute::PHOTO_ID_ATTRIBUTE];
		$this->photo = Photo::query()->findOrFail($photoID);
		$this->license = LicenseType::tryFrom($values[RequestAttribute::LICENSE_ATTRIBUTE]);
	}
}
