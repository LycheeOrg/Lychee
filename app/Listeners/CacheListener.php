<?php

namespace App\Listeners;

use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Support\Facades\Log;

class CacheListener
{
	/**
	 * Create the event listener.
	 */
	public function __construct()
	{
	}

	/**
	 * Handle the event.
	 */
	public function handle(CacheHit|CacheMissed $event): void
	{
		if (str_contains($event->key, 'lv:dev-lycheeOrg')) {
			return;
		}

		match (get_class($event)) {
			CacheMissed::class => Log::info('CacheListener: Miss for ' . $event->key),
			CacheHit::class => Log::info('CacheListener: Hit for ' . $event->key),
			default => '',
		};
	}
}
