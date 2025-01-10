<?php

namespace App\Events;

use App\Enum\CacheTag;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TaggedRouteCacheUpdated
{
	use Dispatchable;
	use InteractsWithSockets;
	use SerializesModels;

	/**
	 * Create a new event instance.
	 */
	public function __construct(public CacheTag $tag)
	{
	}
}
