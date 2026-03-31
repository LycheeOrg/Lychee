<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Webhook;

use App\Assets\Features;
use App\Enum\PhotoWebhookEvent;
use App\Enum\SizeVariantType;
use App\Enum\WebhookMethod;
use App\Enum\WebhookPayloadFormat;
use App\Http\Requests\BaseApiRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

/**
 * Request for creating a new webhook configuration.
 */
class StoreWebhookRequest extends BaseApiRequest
{
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

	public function rules(): array
	{
		return [
			'name' => ['required', 'string', 'max:255'],
			'event' => ['required', 'string', new Enum(PhotoWebhookEvent::class)],
			'method' => ['required', 'string', new Enum(WebhookMethod::class)],
			'url' => ['required', 'string', 'url', 'max:2048'],
			'payload_format' => ['required', 'string', new Enum(WebhookPayloadFormat::class)],
			'secret' => ['sometimes', 'nullable', 'string', 'max:1024'],
			'secret_header' => ['sometimes', 'nullable', 'string', 'max:255', 'regex:/^[a-zA-Z0-9\-]+$/'],
			'enabled' => ['sometimes', 'boolean'],
			'send_photo_id' => ['sometimes', 'boolean'],
			'send_album_id' => ['sometimes', 'boolean'],
			'send_title' => ['sometimes', 'boolean'],
			'send_size_variants' => ['sometimes', 'boolean'],
			'size_variant_types' => ['sometimes', 'nullable', 'array'],
			'size_variant_types.*' => ['integer', new Enum(SizeVariantType::class)],
		];
	}

	protected function processValidatedValues(array $values, array $files): void
	{
		// No pre-processing needed; validated values are used directly.
	}
}
