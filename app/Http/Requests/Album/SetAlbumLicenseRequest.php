<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAbstractAlbum;
use App\Http\Requests\Contracts\HasAlbum;
use App\Http\Requests\Contracts\HasLicense;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Http\Requests\Traits\HasLicenseTrait;
use App\Models\Album;
use App\Rules\LicenseRule;
use App\Rules\RandomIDRule;

class SetAlbumLicenseRequest extends BaseApiRequest implements HasAlbum, HasLicense
{
	use HasAlbumTrait;
	use HasLicenseTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumWrite($this->album);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAbstractAlbum::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			HasLicense::LICENSE_ATTRIBUTE => ['required', new LicenseRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = Album::query()->findOrFail($values[HasAbstractAlbum::ALBUM_ID_ATTRIBUTE]);
		$this->license = $values[HasLicense::LICENSE_ATTRIBUTE];
	}
}
