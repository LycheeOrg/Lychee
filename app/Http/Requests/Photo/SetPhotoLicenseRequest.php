<?php

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\HasLicense;
use App\Contracts\Http\Requests\HasPhoto;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
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
			RequestAttribute::PHOTO_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::LICENSE_ATTRIBUTE => ['required', new LicenseRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photo = Photo::query()->findOrFail($values[RequestAttribute::PHOTO_ID_ATTRIBUTE]);
		$this->license = $values[RequestAttribute::LICENSE_ATTRIBUTE];
	}
}
