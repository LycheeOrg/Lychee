<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Listeners;

use App\Models\Configs;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyForgotten;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Support\Facades\Log;

/**
 * Just logging of Cache events.
 */
class CacheListener
{
	/**
	 * Handle the event.
	 */
	public function handle(CacheHit|CacheMissed|KeyForgotten|KeyWritten $event): void
	{
		if (str_contains($event->key, 'lv:dev-lycheeOrg')) {
			return;
		}

		if (Configs::getValueAsBool('cache_event_logging') === false) {
			return;
		}

		match (get_class($event)) {
			CacheMissed::class => Log::debug('CacheListener: Miss for ' . $event->key),
			CacheHit::class => Log::debug('CacheListener: Hit for ' . $event->key),
			KeyForgotten::class => Log::info('CacheListener: Forgetting key ' . $event->key),
			KeyWritten::class => $this->keyWritten($event),
			default => '',
		};
	}

	private function keyWritten(KeyWritten $event): void
	{
		if (!str_starts_with($event->key, 'api/')) {
			Log::info('CacheListener: Writing key ' . $event->key);

			return;
		}

		Log::debug('CacheListener: Writing key ' . $event->key . ' with value: ' . var_export($event->value, true));
	}
}
