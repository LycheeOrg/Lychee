<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumID;
use App\Http\Requests\Contracts\HasAlbumModelID;
use App\Http\Requests\Contracts\HasLicense;
use App\Http\Requests\Traits\HasAlbumModelIDTrait;
use App\Http\Requests\Traits\HasLicenseTrait;
use App\Rules\LicenseRule;
use App\Rules\ModelIDRule;

class SetAlbumLicenseRequest extends BaseApiRequest implements HasAlbumModelID, HasLicense
{
	use HasAlbumModelIDTrait;
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
			HasAlbumID::ALBUM_ID_ATTRIBUTE => ['required', new ModelIDRule(false)],
			HasLicense::LICENSE_ATTRIBUTE => ['required', new LicenseRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumID = intval($values[HasAlbumID::ALBUM_ID_ATTRIBUTE]);
		$this->license = $values[HasLicense::LICENSE_ATTRIBUTE];
	}
}
