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
use App\Policies\AiVisionPolicy;
use App\Rules\RandomIDRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

// TODO: Make sure FacePermissionMode applies here
class ToggleDismissedRequest extends BaseApiRequest implements HasFace
{
	use HasFaceTrait;

	public function authorize(): bool
	{
		if (!Gate::check(AiVisionPolicy::CAN_DISMISS_FACE, Face::class)) {
			return false;
		}

		$user = Auth::user();

		return ($user?->may_administrate === true) || $this->face->photo->owner_id === $user?->id;
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
