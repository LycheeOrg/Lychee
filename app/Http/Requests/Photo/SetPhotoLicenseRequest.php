<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasLicense;
use App\Http\Requests\Contracts\HasPhoto;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditPhotoTrait;
use App\Http\Requests\Traits\HasLicenseTrait;
use App\Http\Requests\Traits\HasPhotoTrait;
use App\Models\Photo;
use App\Rules\LicenseRule;
use App\Rules\RandomIDRule;

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
		return [
			HasPhoto::PHOTO_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			HasLicense::LICENSE_ATTRIBUTE => ['required', new LicenseRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photo = Photo::query()->findOrFail($values[HasPhoto::PHOTO_ID_ATTRIBUTE]);
		$this->license = $values[HasLicense::LICENSE_ATTRIBUTE];
	}
}
