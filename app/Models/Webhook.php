<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Models;

use App\Enum\PhotoWebhookEvent;
use App\Enum\WebhookMethod;
use App\Enum\WebhookPayloadFormat;
use App\Models\Extensions\ThrowsConsistentExceptions;
use App\Models\Extensions\UTCBasedTimes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Webhook.
 *
 * Represents an outgoing webhook configuration.
 * Webhooks are hard-deleted — no soft-delete.
 *
 * @property string               $id                 ULID primary key
 * @property string               $name               Human-readable label
 * @property PhotoWebhookEvent    $event              Lifecycle event that triggers this webhook
 * @property WebhookMethod        $method             HTTP method for the outgoing request
 * @property string               $url                Target URL (HTTP or HTTPS)
 * @property WebhookPayloadFormat $payload_format     How the payload is delivered (json | query_string)
 * @property string|null          $secret             Secret key (encrypted at rest)
 * @property string|null          $secret_header      HTTP header name that carries the secret
 * @property bool                 $enabled            When false, this webhook is skipped during dispatch
 * @property bool                 $send_photo_id      Include photo_id in payload
 * @property bool                 $send_album_id      Include album_id in payload
 * @property bool                 $send_title         Include title in payload
 * @property bool                 $send_size_variants Include size_variants in payload
 * @property int[]|null           $size_variant_types SizeVariantType int values to include
 * @property Carbon               $created_at
 * @property Carbon               $updated_at
 */
class Webhook extends Model
{
	/** @phpstan-use HasFactory<\Database\Factories\WebhookFactory> */
	use HasFactory;
	use UTCBasedTimes;
	use ThrowsConsistentExceptions;

	public $incrementing = false;
	protected $keyType = 'string';

	/**
	 * @var list<string>
	 */
	protected $fillable = [
		'name',
		'event',
		'method',
		'url',
		'payload_format',
		'secret',
		'secret_header',
		'enabled',
		'send_photo_id',
		'send_album_id',
		'send_title',
		'send_size_variants',
		'size_variant_types',
	];

	/**
	 * @var array<string, string>
	 */
	protected $casts = [
		'event' => PhotoWebhookEvent::class,
		'method' => WebhookMethod::class,
		'payload_format' => WebhookPayloadFormat::class,
		'secret' => 'encrypted',
		'enabled' => 'boolean',
		'send_photo_id' => 'boolean',
		'send_album_id' => 'boolean',
		'send_title' => 'boolean',
		'send_size_variants' => 'boolean',
		'size_variant_types' => 'array',
		'created_at' => 'datetime',
		'updated_at' => 'datetime',
	];

	/**
	 * @var list<string>
	 */
	protected $hidden = [
		'secret',
	];

	/**
	 * Boot model: generate ULID for new records.
	 */
	protected static function boot(): void
	{
		parent::boot();

		static::creating(function (Webhook $webhook): void {
			if ($webhook->id === null || $webhook->id === '') {
				$webhook->id = (string) \Illuminate\Support\Str::ulid();
			}
		});
	}

	/**
	 * Scope to only enabled webhooks.
	 *
	 * @param Builder<Webhook> $query
	 *
	 * @return Builder<Webhook>
	 */
	public function scopeEnabled(Builder $query): Builder
	{
		return $query->where('enabled', '=', true);
	}

	/**
	 * Scope to webhooks matching a specific lifecycle event.
	 *
	 * @param Builder<Webhook>  $query
	 * @param PhotoWebhookEvent $event
	 *
	 * @return Builder<Webhook>
	 */
	public function scopeForEvent(Builder $query, PhotoWebhookEvent $event): Builder
	{
		return $query->where('event', '=', $event->value);
	}
}
