<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Requests\Photo;

use App\Contracts\Http\Requests\HasDescription;
use App\Contracts\Http\Requests\HasLicense;
use App\Contracts\Http\Requests\HasPhoto;
use App\Contracts\Http\Requests\HasTags;
use App\Contracts\Http\Requests\HasTakenAt;
use App\Contracts\Http\Requests\HasTitle;
use App\Contracts\Http\Requests\HasUploadDate;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Enum\LicenseType;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasDescriptionTrait;
use App\Http\Requests\Traits\HasLicenseTrait;
use App\Http\Requests\Traits\HasPhotoTrait;
use App\Http\Requests\Traits\HasTagsTrait;
use App\Http\Requests\Traits\HasTakenAtDateTrait;
use App\Http\Requests\Traits\HasTitleTrait;
use App\Http\Requests\Traits\HasUploadDateTrait;
use App\Models\Photo;
use App\Policies\PhotoPolicy;
use App\Rules\DescriptionRule;
use App\Rules\RandomIDRule;
use App\Rules\TitleRule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Enum;

class EditPhotoRequest extends BaseApiRequest implements HasPhoto, HasTags, HasUploadDate, HasDescription, HasLicense, HasTitle, HasTakenAt
{
	use HasPhotoTrait;
	use HasTitleTrait;
	use HasDescriptionTrait;
	use HasTagsTrait;
	use HasUploadDateTrait;
	use HasLicenseTrait;
	use HasTakenAtDateTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(PhotoPolicy::CAN_EDIT, $this->photo);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			RequestAttribute::PHOTO_ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
			RequestAttribute::TITLE_ATTRIBUTE => ['required', new TitleRule()],
			RequestAttribute::DESCRIPTION_ATTRIBUTE => ['present', new DescriptionRule()],
			RequestAttribute::TAGS_ATTRIBUTE => ['present', 'array'],
			RequestAttribute::TAGS_ATTRIBUTE . '.*' => ['required', 'string', 'min:1'],
			RequestAttribute::LICENSE_ATTRIBUTE => ['required', new Enum(LicenseType::class)],
			RequestAttribute::UPLOAD_DATE_ATTRIBUTE => ['required', 'date'],
			RequestAttribute::TAKEN_DATE_ATTRIBUTE => ['nullable', 'date'],
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var string $photoID */
		$photoID = $values[RequestAttribute::PHOTO_ID_ATTRIBUTE];

		$this->photo = Photo::query()
			->with(['size_variants', 'size_variants.sym_links'])
			->findOrFail($photoID);

		$this->title = $values[RequestAttribute::TITLE_ATTRIBUTE];
		$this->description = $values[RequestAttribute::DESCRIPTION_ATTRIBUTE];
		$this->tags = $values[RequestAttribute::TAGS_ATTRIBUTE];
		$this->upload_date = Carbon::parse($values[RequestAttribute::UPLOAD_DATE_ATTRIBUTE]);
		$this->license = LicenseType::tryFrom($values[RequestAttribute::LICENSE_ATTRIBUTE]);

		// We only set this one if it is not null
		if (isset($values[RequestAttribute::TAKEN_DATE_ATTRIBUTE])) {
			$this->taken_at = Carbon::parse($values[RequestAttribute::TAKEN_DATE_ATTRIBUTE]);
		}
	}
}
