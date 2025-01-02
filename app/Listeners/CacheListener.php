<?php

namespace App\Listeners;

use App\Models\Configs;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyForgotten;
use Illuminate\Support\Facades\Log;

class CacheListener
{
	/**
	 * Handle the event.
	 */
	public function handle(CacheHit|CacheMissed|KeyForgotten $event): void
	{
		if (str_contains($event->key, 'lv:dev-lycheeOrg')) {
			return;
		}

		if (Configs::getValueAsBool('cache_event_logging') === false) {
			return;
		}

		match (get_class($event)) {
			CacheMissed::class => Log::info('CacheListener: Miss for ' . $event->key),
			CacheHit::class => Log::info('CacheListener: Hit for ' . $event->key),
			KeyForgotten::class => Log::info('CacheListener: Forgetting key ' . $event->key),
			default => '',
		};
	}
}
