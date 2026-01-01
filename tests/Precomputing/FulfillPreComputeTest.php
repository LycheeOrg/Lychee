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

namespace Tests\Precomputing;

use App\Jobs\RecomputeAlbumStatsJob;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Queue;
use Tests\Precomputing\Base\BasePrecomputingTest;

/**
 * Test FulfillPreCompute maintenance controller.
 */
class FulfillPreComputeTest extends BasePrecomputingTest
{
	/**
	 * Test unauthorized access (guest).
	 *
	 * @return void
	 */
	public function testGuest(): void
	{
		$response = $this->getJsonWithData('Maintenance::fulfillPrecompute');
		$this->assertUnauthorized($response);

		$response = $this->postJson('Maintenance::fulfillPrecompute');
		$this->assertUnauthorized($response);
	}

	/**
	 * Test forbidden access (regular user).
	 *
	 * @return void
	 */
	public function testUser(): void
	{
		$user = User::factory()->create();
		$response = $this->actingAs($user)->getJsonWithData('Maintenance::fulfillPrecompute');
		$this->assertForbidden($response);

		$response = $this->actingAs($user)->postJson('Maintenance::fulfillPrecompute');
		$this->assertForbidden($response);
	}

	/**
	 * Test check endpoint returns count of albums needing computation.
	 *
	 * @return void
	 */
	public function testCheckReturnsCount(): void
	{
		$user = User::factory()->create();

		// Create an album with all null/default precomputed fields
		$album = Album::factory()->as_root()->owned_by($user)->create([
			'max_taken_at' => null,
			'min_taken_at' => null,
			'num_children' => 0,
			'num_photos' => 0,
			'auto_cover_id_max_privilege' => null,
			'auto_cover_id_least_privilege' => null,
		]);

		$response = $this->actingAs($this->admin)->getJsonWithData('Maintenance::fulfillPrecompute');
		$this->assertOk($response);

		// Should return at least 1 (the album we just created)
		$count = $response->json();
		$this->assertGreaterThanOrEqual(1, $count);
	}

	/**
	 * Test check endpoint excludes albums with computed fields.
	 *
	 * @return void
	 */
	public function testCheckExcludesComputedAlbums(): void
	{
		$user = User::factory()->create();

		// Get initial count
		$initialResponse = $this->actingAs($this->admin)->getJsonWithData('Maintenance::fulfillPrecompute');
		$initialCount = $initialResponse->json();

		// Create an album with computed fields (not all null)
		$photo = Photo::factory()->owned_by($user)->create();
		$album = Album::factory()->as_root()->owned_by($user)->create();

		// Manually set computed fields to avoid casting issues
		$album->max_taken_at = Carbon::parse('2023-01-01');
		$album->min_taken_at = Carbon::parse('2023-01-01');
		$album->num_children = 0;
		$album->num_photos = 1;
		$album->auto_cover_id_max_privilege = $photo->id;
		$album->auto_cover_id_least_privilege = $photo->id;
		$album->save();

		// Count should stay the same since the new album has computed fields
		$response = $this->actingAs($this->admin)->getJsonWithData('Maintenance::fulfillPrecompute');
		$this->assertOk($response);
		$count = $response->json();
		$this->assertEquals($initialCount, $count);
	}

	/**
	 * Test do endpoint dispatches jobs for albums needing computation.
	 *
	 * @return void
	 */
	public function testDoDispatchesJobs(): void
	{
		Queue::fake();

		$user = User::factory()->create();

		// Create albums needing computation
		$album1 = Album::factory()->as_root()->owned_by($user)->create([
			'max_taken_at' => null,
			'min_taken_at' => null,
			'num_children' => 0,
			'num_photos' => 0,
			'auto_cover_id_max_privilege' => null,
			'auto_cover_id_least_privilege' => null,
		]);

		$album2 = Album::factory()->as_root()->owned_by($user)->create([
			'max_taken_at' => null,
			'min_taken_at' => null,
			'num_children' => 0,
			'num_photos' => 0,
			'auto_cover_id_max_privilege' => null,
			'auto_cover_id_least_privilege' => null,
		]);

		$response = $this->actingAs($this->admin)->postJson('Maintenance::fulfillPrecompute');
		$this->assertNoContent($response);

		// Assert jobs were dispatched for at least our 2 albums
		Queue::assertPushed(RecomputeAlbumStatsJob::class, function ($job) use ($album1) {
			return $job->album_id === $album1->id;
		});
		Queue::assertPushed(RecomputeAlbumStatsJob::class, function ($job) use ($album2) {
			return $job->album_id === $album2->id;
		});
	}

	/**
	 * Test do endpoint skips albums that already have computed fields.
	 *
	 * @return void
	 */
	public function testDoSkipsComputedAlbums(): void
	{
		Queue::fake();

		$user = User::factory()->create();
		$photo = Photo::factory()->owned_by($user)->create();

		// Create album with computed fields
		$album = Album::factory()->as_root()->owned_by($user)->create();

		// Manually set computed fields to avoid casting issues
		$album->max_taken_at = Carbon::parse('2023-01-01');
		$album->min_taken_at = Carbon::parse('2023-01-01');
		$album->num_children = 0;
		$album->num_photos = 1;
		$album->auto_cover_id_max_privilege = $photo->id;
		$album->auto_cover_id_least_privilege = $photo->id;
		$album->save();

		$response = $this->actingAs($this->admin)->postJson('Maintenance::fulfillPrecompute');
		$this->assertNoContent($response);

		// Assert no job dispatched for our specific album (it already has computed fields)
		Queue::assertNotPushed(RecomputeAlbumStatsJob::class, function ($job) use ($album) {
			return $job->album_id === $album->id;
		});
	}

	/**
	 * Test integration: do endpoint actually computes fields.
	 *
	 * @return void
	 */
	public function testIntegrationComputesFields(): void
	{
		$user = User::factory()->create();

		// Create album with photos but null computed fields
		$album = Album::factory()->as_root()->owned_by($user)->create([
			'max_taken_at' => null,
			'min_taken_at' => null,
			'num_children' => 0,
			'num_photos' => 0,
			'auto_cover_id_max_privilege' => null,
			'auto_cover_id_least_privilege' => null,
		]);

		$photo1 = Photo::factory()->owned_by($user)->create();
		$photo2 = Photo::factory()->owned_by($user)->create();

		$photo1->albums()->attach($album->id);
		$photo2->albums()->attach($album->id);

		// Call the do endpoint (jobs will run synchronously in tests)
		$response = $this->actingAs($this->admin)->postJson('Maintenance::fulfillPrecompute');
		$this->assertNoContent($response);

		// Verify fields were computed
		$album->refresh();
		$this->assertEquals(2, $album->num_photos);
	}

	/**
	 * Test admin can access both endpoints.
	 *
	 * @return void
	 */
	public function testAdmin(): void
	{
		$response = $this->actingAs($this->admin)->getJsonWithData('Maintenance::fulfillPrecompute');
		$this->assertOk($response);

		$response = $this->actingAs($this->admin)->postJson('Maintenance::fulfillPrecompute');
		$this->assertNoContent($response);
	}
}
