<?php

namespace App\Http\Requests\Album;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbum;
use App\Http\Requests\Contracts\HasLicense;
use App\Http\Requests\Contracts\RequestAttribute;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditAlbumTrait;
use App\Http\Requests\Traits\HasAlbumTrait;
use App\Http\Requests\Traits\HasLicenseTrait;
use App\Models\Album;
use App\Rules\LicenseRule;
use App\Rules\RandomIDRule;

class SetAlbumLicenseRequest extends BaseApiRequest implements HasAlbum, HasLicense
{
	use HasAlbumTrait;
	use HasLicenseTrait;
	use AuthorizeCanEditAlbumTrait;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::ALBUM_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::LICENSE_ATTRIBUTE => ['required', new LicenseRule()],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->album = Album::query()->findOrFail($values[RequestAttribute::ALBUM_ID_ATTRIBUTE]);
		$this->license = $values[RequestAttribute::LICENSE_ATTRIBUTE];
	}
}
