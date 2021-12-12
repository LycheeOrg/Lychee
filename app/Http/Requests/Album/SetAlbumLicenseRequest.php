<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumID;
use App\Http\Requests\Contracts\HasLicense;
use App\Http\Requests\Traits\HasAlbumIDTrait;
use App\Http\Requests\Traits\HasLicenseTrait;
use App\Rules\LicenseRule;
use App\Rules\RandomIDRule;

class SetAlbumLicenseRequest extends BaseApiRequest implements HasAlbumID, HasLicense
{
	use HasAlbumIDTrait;
	use HasLicenseTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumWrite([$this->albumID]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAlbumID::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			HasLicense::LICENSE_ATTRIBUTE => ['required', new LicenseRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumID = $values[HasAlbumID::ALBUM_ID_ATTRIBUTE];
		$this->license = $values[HasLicense::LICENSE_ATTRIBUTE];
	}
}
