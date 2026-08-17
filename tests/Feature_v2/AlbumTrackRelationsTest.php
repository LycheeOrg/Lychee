<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v2;

use App\Models\Album;
use App\Models\Track;
use App\Models\User;
use Tests\Feature_v2\Base\BaseApiTest;

class AlbumTrackRelationsTest extends BaseApiTest
{
	private Album $album;

	protected function setUp(): void
	{
		parent::setUp();
		$owner = User::factory()->create();
		$this->album = Album::factory()->as_root()->create(['owner_id' => $owner->id]);
	}

	public function testTracksReturnsAllInUploadOrder(): void
	{
		$t1 = Track::factory()->for_album($this->album)->primary()->create();
		$t2 = Track::factory()->for_album($this->album)->create();
		$t3 = Track::factory()->for_album($this->album)->create();

		$ids = $this->album->tracks()->get()->pluck('id')->all();

		self::assertSame([$t1->id, $t2->id, $t3->id], $ids);
	}

	public function testPrimaryTrackResolvesToTheFlaggedRow(): void
	{
		Track::factory()->for_album($this->album)->create();
		$primary = Track::factory()->for_album($this->album)->primary()->create();
		Track::factory()->for_album($this->album)->create();

		self::assertSame($primary->id, $this->album->primaryTrack()->first()?->id);
	}

	public function testPrimaryTrackIsNullWhenNoneFlagged(): void
	{
		Track::factory()->for_album($this->album)->create();
		Track::factory()->for_album($this->album)->create();

		self::assertNull($this->album->primaryTrack()->first());
	}

	public function testAtMostOnePrimaryTrackPerAlbumInvariant(): void
	{
		Track::factory()->for_album($this->album)->primary()->create();
		Track::factory()->for_album($this->album)->create();
		Track::factory()->for_album($this->album)->create();

		$primaryCount = $this->album->tracks()->where('is_primary', true)->count();

		self::assertSame(1, $primaryCount);
	}
}
