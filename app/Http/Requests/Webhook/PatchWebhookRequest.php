<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Webhook;

use App\Assets\Features;
use App\Enum\PhotoWebhookEvent;
use App\Enum\WebhookMethod;
use App\Enum\WebhookPayloadFormat;
use App\Http\Requests\BaseApiRequest;
use App\Models\User;
use App\Models\Webhook;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

/**
 * Request for a partial update of an existing webhook configuration.
 * All fields are optional — only supplied fields are validated and updated.
 */
class PatchWebhookRequest extends BaseApiRequest
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
			'webhook_id' => ['required', 'string', 'exists:webhooks,id'],
			'name' => ['sometimes', 'required', 'string', 'max:255'],
			'event' => ['sometimes', 'required', 'string', Rule::in(PhotoWebhookEvent::values())],
			'method' => ['sometimes', 'required', 'string', Rule::in(WebhookMethod::values())],
			'url' => ['sometimes', 'required', 'string', 'url', 'max:2048'],
			'payload_format' => ['sometimes', 'required', 'string', Rule::in(WebhookPayloadFormat::values())],
			'secret' => ['sometimes', 'nullable', 'string', 'max:1024'],
			'secret_header' => ['sometimes', 'nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\-]+$/'],
			'enabled' => ['sometimes', 'boolean'],
			'send_photo_id' => ['sometimes', 'boolean'],
			'send_album_id' => ['sometimes', 'boolean'],
			'send_title' => ['sometimes', 'boolean'],
			'send_size_variants' => ['sometimes', 'boolean'],
			'size_variant_types' => ['sometimes', 'nullable', 'array'],
			'size_variant_types.*' => ['integer', Rule::in(array_column(\App\Enum\SizeVariantType::cases(), 'value'))],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		$this->webhook = Webhook::findOrFail($values['webhook_id']);
	}
}
