<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Webhook;

use App\Assets\Features;
use App\Http\Requests\BaseApiRequest;
use App\Models\User;
use App\Models\Webhook;
use Illuminate\Support\Facades\Auth;

/**
 * Request for retrieving a single webhook configuration.
 */
class ShowWebhookRequest extends BaseApiRequest
{
	public Webhook $webhook;

	/**
	 * Only administrators may manage webhooks, and the webhook feature must be enabled.
	 */
	public function authorize(): bool
	{
		if (Features::inactive('webhook')) {
			return false;
		}

		/** @var User|null */
		$user = Auth::user();

		return $user?->may_administrate === true;
	}

	protected function prepareForValidation(): void
	{
		/** @disregard */
		$this->merge([
			'webhook_id' => $this->route('webhook'),
		]);
	}

	public function rules(): array
	{
		return [
			'webhook_id' => ['required', 'string'],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->webhook = Webhook::findOrFail($values['webhook_id']);
	}
}
