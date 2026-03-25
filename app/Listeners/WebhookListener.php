<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Listeners;

use App\DTO\WebhookPayload;
use App\Enum\PhotoWebhookEvent;
use App\Events\PhotoAdded;
use App\Events\PhotoMoved;
use App\Events\PhotoWillBeDeleted;
use App\Jobs\WebhookDispatchJob;
use App\Models\SizeVariant;
use App\Models\Webhook;
use App\Services\Webhook\WebhookPayloadBuilder;
use Illuminate\Support\Facades\Log;

/**
 * Listens to photo lifecycle domain events and dispatches WebhookDispatchJob
 * for each enabled, matching webhook configuration.
 */
class WebhookListener
{
	public function __construct(private readonly WebhookPayloadBuilder $builder)
	{
	}

	/**
	 * Handle the PhotoAdded event (photo.add webhooks).
	 */
	public function handlePhotoAdded(PhotoAdded $event): void
	{
		$this->dispatchForEvent(
			PhotoWebhookEvent::ADD,
			$event->photo_id,
		);
	}

	/**
	 * Handle the PhotoMoved event (photo.move webhooks).
	 */
	public function handlePhotoMoved(PhotoMoved $event): void
	{
		$this->dispatchForEvent(
			PhotoWebhookEvent::MOVE,
			$event->photo_id,
		);
	}

	/**
	 * Handle the PhotoWillBeDeleted event (photo.delete webhooks).
	 *
	 * Uses the snapshot carried by the event — no DB load needed.
	 */
	public function handlePhotoWillBeDeleted(PhotoWillBeDeleted $event): void
	{
		$webhooks = Webhook::query()
			->enabled()
			->forEvent(PhotoWebhookEvent::DELETE)
			->get();

		if ($webhooks->isEmpty()) {
			return;
		}

		foreach ($webhooks as $webhook) {
			$payload = $this->builder->build(
				webhook: $webhook,
				photo_id: $event->photo_id,
				album_id: $event->album_id,
				title: $event->title,
				size_variants: $event->size_variants,
			);
			WebhookDispatchJob::dispatch($webhook, $payload);
		}
	}

	/**
	 * Load the photo's data from the DB and dispatch jobs for all matching webhooks.
	 *
	 * Used by handlePhotoAdded and handlePhotoMoved where the photo still exists in DB.
	 */
	private function dispatchForEvent(PhotoWebhookEvent $event_type, string $photo_id): void
	{
		$webhooks = Webhook::query()
			->enabled()
			->forEvent($event_type)
			->get();

		if ($webhooks->isEmpty()) {
			return;
		}

		// Load the photo and its albums to build the payload.
		/** @var \App\Models\Photo|null $photo */
		$photo = \App\Models\Photo::query()
			->with(['albums', 'size_variants'])
			->find($photo_id);

		if ($photo === null) {
			Log::warning('WebhookListener: photo not found', ['photo_id' => $photo_id, 'event' => $event_type->value]);

			return;
		}

		// Get the primary album id (first album if multiple).
		$album_id = $photo->albums->first()?->id ?? '';

		// Build snapshot of size variants.
		$size_variants_snapshot = $this->buildSizeVariantsSnapshot($photo);

		foreach ($webhooks as $webhook) {
			$payload = $this->builder->build(
				webhook: $webhook,
				photo_id: $photo->id,
				album_id: $album_id,
				title: $photo->title,
				size_variants: $size_variants_snapshot,
			);
			WebhookDispatchJob::dispatch($webhook, $payload);
		}
	}

	/**
	 * Build a size_variants snapshot array from a loaded Photo model.
	 *
	 * @param \App\Models\Photo $photo
	 *
	 * @return array<int,array{type:string,url:string}>
	 */
	private function buildSizeVariantsSnapshot(\App\Models\Photo $photo): array
	{
		$variants = [];
		/** @var SizeVariant $sv */
		foreach ($photo->size_variants->toCollection() as $sv) {
			if ($sv === null) {
				continue;
			}
			$variants[] = [
				'type' => $sv->type->name(),
				'url' => $sv->url,
			];
		}

		return $variants;
	}
}
