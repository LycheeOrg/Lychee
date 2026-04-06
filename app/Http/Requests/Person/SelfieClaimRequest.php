<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Person;

use App\Http\Requests\BaseApiRequest;
use App\Models\Person;
use App\Policies\AiVisionPolicy;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;

class SelfieClaimRequest extends BaseApiRequest
{
	private UploadedFile $selfie;

	public function authorize(): bool
	{
		return Gate::check(AiVisionPolicy::CAN_CLAIM_PERSON, Person::class);
	}

	public function rules(): array
	{
		return [
			'selfie' => ['required', 'file', 'image', 'max:10240'],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		/** @var UploadedFile $selfie */
		$selfie = $files['selfie'];
		$this->selfie = $selfie;
	}

	public function selfie(): UploadedFile
	{
		return $this->selfie;
	}
}
