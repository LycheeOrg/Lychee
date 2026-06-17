<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Face;

use App\Contracts\Http\Requests\HasPhoto;
use App\Contracts\Http\Requests\RequestAttribute;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasPhotoTrait;
use App\Models\Photo;
use App\Policies\PhotoPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;

class GetPhotoFacesRequest extends BaseApiRequest implements HasPhoto
{
	use HasPhotoTrait;

	public function authorize(): bool
	{
		return Gate::check(PhotoPolicy::CAN_VIEW_FACE_OVERLAYS, $this->photo);
	}

	public function rules(): array
	{
		return [
			RequestAttribute::ID_ATTRIBUTE => ['required', new RandomIDRule(false)],
		];
	}

	protected function prepareForValidation(): void
	{
		/** @disregard */
		$this->merge([RequestAttribute::ID_ATTRIBUTE => $this->route(RequestAttribute::ID_ATTRIBUTE)]);
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->photo = Photo::query()
			->with(['faces.person', 'faces.suggestions.suggestedFace.person', 'albums.access_permissions'])
			->findOrFail($values[RequestAttribute::ID_ATTRIBUTE]);
	}
}
