<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Legacy\V1\Contracts\Http\Requests\HasPhotoIDs;
use App\Legacy\V1\Contracts\Http\Requests\RequestAttribute;
use App\Legacy\V1\Requests\Traits\HasPhotoIDsTrait;
use App\Legacy\V1\RuleSets\Photo\DeletePhotosRuleSet;
use App\Models\Photo;
use App\Policies\PhotoPolicy;
use Illuminate\Support\Facades\Gate;

final class DeletePhotosRequest extends BaseApiRequest implements HasPhotoIDs
{
	use HasPhotoIDsTrait;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return Gate::check(PhotoPolicy::CAN_EDIT_ID, [Photo::class, $this->photoIDs()]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return DeletePhotosRuleSet::rules();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		// As we are going to delete the photos anyway, we don't load the
		// models for efficiency reasons.
		// Instead, we use mass deletion via low-level SQL queries later.
		$this->photoIDs = $values[RequestAttribute::PHOTO_IDS_ATTRIBUTE];
	}
}
