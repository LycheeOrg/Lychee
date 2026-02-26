<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\HasLicense;
use App\Contracts\Http\Requests\HasPhotoIds;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\LicenseType;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\Authorize\AuthorizeCanEditPhotosByIdTrait;
use App\Http\Requests\Traits\HasLicenseTrait;
use App\Http\Requests\Traits\HasPhotoIdsTrait;
use App\Rules\RandomIDRule;
use Illuminate\Validation\Rules\Enum;

class SetPhotosLicenseRequest extends BaseApiRequest implements HasPhotoIds, HasLicense
{
	use HasPhotoIdsTrait;
	use HasLicenseTrait;
	use AuthorizeCanEditPhotosByIdTrait;

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::PHOTO_IDS_ATTRIBUTE => 'required|array|min:1',
			RequestAttribute::PHOTO_IDS_ATTRIBUTE . '.*' => ['required', new RandomIDRule(false)],
			RequestAttribute::LICENSE_ATTRIBUTE => ['required', new Enum(LicenseType::class)],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photo_ids = $values[RequestAttribute::PHOTO_IDS_ATTRIBUTE];
		$this->license = LicenseType::from($values[RequestAttribute::LICENSE_ATTRIBUTE]);
	}
}
