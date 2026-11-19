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

	public const IS_DISMISSED_ATTRIBUTE = 'is_dismissed';

	/**
	 * Target value when the caller sends one (Feature 093, API-093-02);
	 * null keeps the historical toggle behaviour.
	 */
	private ?bool $target_dismissed = null;

	public function targetDismissed(): ?bool
	{
		return $this->target_dismissed;
	}

	public function authorize(): bool
	{
		return Gate::check(PhotoPolicy::CAN_DISMISS_FACE, $this->face->photo);
	}

	public function rules(): array
	{
		return [
			'id' => ['required', new RandomIDRule(false)],
			self::IS_DISMISSED_ATTRIBUTE => ['sometimes', 'boolean'],
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
		$this->target_dismissed = array_key_exists(self::IS_DISMISSED_ATTRIBUTE, $values) ? in_array($values[self::IS_DISMISSED_ATTRIBUTE], [true, 1, '1', 'true'], true) : null;
	}
}
