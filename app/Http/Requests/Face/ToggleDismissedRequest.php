<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Face;

use App\Contracts\Http\Requests\HasFace;
use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Traits\HasFaceTrait;
use App\Models\Face;
use App\Policies\PhotoPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Gate;

class ToggleDismissedRequest extends BaseApiRequest implements HasFace
{
	use HasFaceTrait;

	public function authorize(): bool
	{
		return Gate::check(PhotoPolicy::CAN_DISMISS_FACE, $this->face->photo);
	}

	public function rules(): array
	{
		return [
			'id' => ['required', new RandomIDRule(false)],
		];
	}

	protected function prepareForValidation(): void
	{
		/** @disregard */
		$this->merge(['id' => $this->route('id')]);
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->face = Face::with('photo')->findOrFail($values['id']);
	}
}
