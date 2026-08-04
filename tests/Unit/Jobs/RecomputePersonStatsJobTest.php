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

namespace Tests\Unit\Jobs;

use App\Jobs\DeleteFaceEmbeddingsJob;
use App\Jobs\RecomputePersonStatsJob;
use App\Models\Face;
use App\Models\Person;
use App\Models\Photo;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use Tests\AbstractTestCase;

/**
 * Note: RecomputePersonStatsJob also logs an error for the "inconsistent"
 * states (face_count XOR photo_count is zero). Those branches are not
 * reachable with real data because both counters are derived from the same
 * filtered `Face` query (see handle()), so they are not exercised here.
 */
class RecomputePersonStatsJobTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testUpdatesStatsWhenFacesAndPhotosExist(): void
	{
		$user = User::factory()->create();
		$person = Person::factory()->create(['face_count' => 0, 'photo_count' => 0]);
		Face::factory()->for_person($person)->for_photo(Photo::factory()->owned_by($user)->create())->create();
		Face::factory()->for_person($person)->for_photo(Photo::factory()->owned_by($user)->create())->create();

		(new RecomputePersonStatsJob([$person->id]))->handle();

		$person->refresh();
		self::assertEquals(2, $person->face_count);
		self::assertEquals(2, $person->photo_count);
	}

	public function testDeletesPersonWithNoFacesAtAll(): void
	{
		$person = Person::factory()->create();

		(new RecomputePersonStatsJob([$person->id]))->handle();

		self::assertNull(Person::find($person->id));
	}

	public function testDeletesPersonAndDispatchesEmbeddingCleanupForDismissedFaces(): void
	{
		Bus::fake();

		$user = User::factory()->create();
		$person = Person::factory()->create();
		$dismissed = Face::factory()->for_person($person)->for_photo(Photo::factory()->owned_by($user)->create())->dismissed()->create();

		(new RecomputePersonStatsJob([$person->id]))->handle();

		self::assertNull(Person::find($person->id));
		self::assertNull(Face::find($dismissed->id));
		Bus::assertDispatched(DeleteFaceEmbeddingsJob::class, fn ($job) => $job->face_ids === [$dismissed->id]);
	}
}
