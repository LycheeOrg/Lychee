<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasLicense;
use App\Http\Requests\Contracts\HasPhotoID;
use App\Http\Requests\Traits\HasLicenseTrait;
use App\Http\Requests\Traits\HasPhotoIDTrait;
use App\Rules\LicenseRule;
use App\Rules\ModelIDRule;

class SetPhotoLicenseRequest extends BaseApiRequest implements HasPhotoID, HasLicense
{
	use HasPhotoIDTrait;
	use HasLicenseTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizePhotoWrite([$this->photoID]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasPhotoID::PHOTO_ID_ATTRIBUTE => ['required', new ModelIDRule(false)],
			HasLicense::LICENSE_ATTRIBUTE => ['required', new LicenseRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photoID = intval($values[HasPhotoID::PHOTO_ID_ATTRIBUTE]);
		$this->license = $values[HasLicense::LICENSE_ATTRIBUTE];
	}
}
