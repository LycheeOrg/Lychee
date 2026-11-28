<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v3\Moderation;

use App\Jobs\PurgeTrashJob;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 119 (FR-119-10, API-119-03, S-119-20): Moderation deletes photos
 * without naming an album. The route is v2, reached through the inherited
 * helpers.
 */
class ModerationDeleteTest extends BaseApiWithDataTest
{
	public function testAdminDeletesPhotosWhereverTheyAre(): void
	{
		Bus::fake();
		DB::table('photo_album')->insert(['photo_id' => $this->photo1->id, 'album_id' => $this->album2->id]);
		$photo_ids = [$this->photo1->id, $this->photo2->id, $this->photoUnsorted->id];

		$response = $this->actingAs($this->admin)->deleteJson('Moderation', ['photo_ids' => $photo_ids]);

		$this->assertNoContent($response);
		foreach ($photo_ids as $photo_id) {
			$this->assertDatabaseHas('trashed_photos', ['photo_id' => $photo_id]);
			$this->assertDatabaseMissing('photo_album', ['photo_id' => $photo_id]);
		}
		Bus::assertDispatchedAfterResponse(PurgeTrashJob::class);
	}

	public function testNonAdminIsForbidden(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Moderation', ['photo_ids' => [$this->photo1->id]]);

		$this->assertForbidden($response);
		$this->assertDatabaseMissing('trashed_photos', ['photo_id' => $this->photo1->id]);
	}

	public function testMoreThan500IdsAreRefused(): void
	{
		$photo_ids = array_map(fn (int $i) => str_pad('x' . $i, 24, 'a'), range(1, 501));

		$response = $this->actingAs($this->admin)->deleteJson('Moderation', ['photo_ids' => $photo_ids]);

		$this->assertUnprocessable($response);
	}
}
