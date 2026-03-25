<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Resources\Models;

use App\Enum\PhotoWebhookEvent;
use App\Enum\WebhookMethod;
use App\Enum\WebhookPayloadFormat;
use App\Models\Webhook;
use Carbon\Carbon;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

/**
 * API resource for a Webhook configuration.
 *
 * The raw secret value is never exposed.
 * Instead, `has_secret` (boolean) indicates whether a secret is configured.
 */
#[TypeScript()]
class WebhookResource extends Data
{
	public string $id;
	public string $name;
	public string $event;
	public string $method;
	public string $url;
	public string $payload_format;
	public bool $has_secret;
	public ?string $secret_header;
	public bool $enabled;
	public bool $send_photo_id;
	public bool $send_album_id;
	public bool $send_title;
	public bool $send_size_variants;
	/** @var int[]|null */
	public ?array $size_variant_types;
	public Carbon $created_at;
	public Carbon $updated_at;

	public function __construct(Webhook $webhook)
	{
		$this->id = $webhook->id;
		$this->name = $webhook->name;
		$this->event = $webhook->event->value;
		$this->method = $webhook->method->value;
		$this->url = $webhook->url;
		$this->payload_format = $webhook->payload_format->value;
		$this->has_secret = $webhook->secret !== null;
		$this->secret_header = $webhook->secret_header;
		$this->enabled = $webhook->enabled;
		$this->send_photo_id = $webhook->send_photo_id;
		$this->send_album_id = $webhook->send_album_id;
		$this->send_title = $webhook->send_title;
		$this->send_size_variants = $webhook->send_size_variants;
		$this->size_variant_types = $webhook->size_variant_types;
		$this->created_at = $webhook->created_at;
		$this->updated_at = $webhook->updated_at;
	}
}
