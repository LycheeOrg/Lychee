<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Unit\Listeners;

use App\Events\AccessPermissionChanged;
use App\Jobs\RecomputeAlbumStatsJob;
use App\Listeners\RecomputeAlbumStatsOnAccessPermissionChange;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Tests\AbstractTestCase;

/**
 * Feature 076, FR-076-13: a change of an album's access permissions
 * recomputes it (and, through the job's propagation, its ancestors).
 */
class RecomputeAlbumStatsOnAccessPermissionChangeTest extends AbstractTestCase
{
	public function testDispatchesOneRecomputeJobForTheAlbum(): void
	{
		Queue::fake();

		(new RecomputeAlbumStatsOnAccessPermissionChange())->handle(new AccessPermissionChanged('album-id'));

		Queue::assertPushed(RecomputeAlbumStatsJob::class, 1);
		Queue::assertPushed(RecomputeAlbumStatsJob::class, fn (RecomputeAlbumStatsJob $job) => $job->album_id === 'album-id' && $job->propagate_to_parent);
	}

	public function testIsRegisteredOnAccessPermissionChanged(): void
	{
		self::assertTrue(Event::hasListeners(AccessPermissionChanged::class));
		$listeners = array_map(
			fn ($listener) => (new \ReflectionFunction($listener))->getStaticVariables()['listener'] ?? null,
			Event::getListeners(AccessPermissionChanged::class)
		);
		self::assertContains(RecomputeAlbumStatsOnAccessPermissionChange::class . '@handle', $listeners);
	}
}
