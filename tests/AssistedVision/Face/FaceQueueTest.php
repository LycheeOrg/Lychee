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

namespace Tests\AssistedVision\Face;

use App\Models\Configs;
use App\Models\Face;
use App\Models\Person;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * `GET /api/v3/Faces/queue` (Feature 093, API-093-03, FR-093-02, FR-093-12).
 */
class FaceQueueTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
		config(['features.struct-of-array' => true]);

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		Configs::set('ai_vision_face_permission_mode', 'public');
		Configs::set('ai_vision_face_queue_limit', '500');
	}

	public function tearDown(): void
	{
		DB::table('persons')->update(['representative_face_id' => null]);
		DB::table('face_suggestions')->delete();
		DB::table('faces')->delete();
		DB::table('persons')->delete();
		parent::tearDown();
	}

	// ── Authorisation ───────────────────────────────────────────

	public function testGuestIsUnauthorized(): void
	{
		$this->assertUnauthorized($this->getJsonV3('Faces/queue'));
	}

	public function testFlagOffIsForbidden(): void
	{
		config(['features.struct-of-array' => false]);
		$this->assertForbidden($this->actingAs($this->admin)->getJsonV3('Faces/queue'));
	}

	public function testFaceRecognitionOffIsForbidden(): void
	{
		Configs::set('ai_vision_face_enabled', '0');
		$this->assertForbidden($this->actingAs($this->admin)->getJsonV3('Faces/queue'));
	}

	public function testRestrictedModeUserIsForbidden(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'restricted');
		$this->assertForbidden($this->actingAs($this->userMayUpload1)->getJsonV3('Faces/queue'));
	}

	public function testRestrictedModeAdminIsAllowed(): void
	{
		Configs::set('ai_vision_face_permission_mode', 'restricted');
		$this->assertOk($this->actingAs($this->admin)->getJsonV3('Faces/queue'));
	}

	// ── Content ─────────────────────────────────────────────────

	public function testShape(): void
	{
		$face = Face::factory()->for_photo($this->photo1)->with_cluster(3)->create(['laplacian_variance' => 123.5]);

		$response = $this->actingAs($this->admin)->getJsonV3('Faces/queue');
		$this->assertOk($response);
		$response->assertExactJson([
			'ids' => [$face->id],
			'photo_ids' => [$this->photo1->id],
			'xs' => [$face->x],
			'ys' => [$face->y],
			'widths' => [$face->width],
			'heights' => [$face->height],
			'crop_urls' => [$face->crop_url],
			'cluster_labels' => [3],
			'suggested_person_ids' => [null],
			'suggested_person_names' => [null],
			'laplacian_variances' => [123.5],
			'is_truncated' => false,
		]);
	}

	public function testAssignedAndDismissedFacesAreExcluded(): void
	{
		$person = Person::factory()->create();
		Face::factory()->for_photo($this->photo1)->for_person($person)->create();
		Face::factory()->for_photo($this->photo1)->dismissed()->create();
		$open = Face::factory()->for_photo($this->photo1)->create();

		$response = $this->actingAs($this->admin)->getJsonV3('Faces/queue');
		$this->assertOk($response);
		self::assertSame([$open->id], $response->json('ids'));
	}

	public function testOrderingLargestClusterFirstThenConfidenceThenUnclustered(): void
	{
		$small_hi = Face::factory()->for_photo($this->photo1)->with_cluster(1)->with_confidence(0.99)->create();
		$big_lo = Face::factory()->for_photo($this->photo1)->with_cluster(2)->with_confidence(0.6)->create();
		$big_hi = Face::factory()->for_photo($this->photo1)->with_cluster(2)->with_confidence(0.9)->create();
		$noise = Face::factory()->for_photo($this->photo1)->with_cluster(-1)->with_confidence(0.95)->create();
		$none = Face::factory()->for_photo($this->photo1)->with_confidence(0.97)->create();

		$response = $this->actingAs($this->admin)->getJsonV3('Faces/queue');
		$this->assertOk($response);
		self::assertSame(
			[$big_hi->id, $big_lo->id, $small_hi->id, $none->id, $noise->id],
			$response->json('ids')
		);
	}

	public function testClusterFilter(): void
	{
		$in = Face::factory()->for_photo($this->photo1)->with_cluster(7)->create();
		Face::factory()->for_photo($this->photo1)->with_cluster(8)->create();
		Face::factory()->for_photo($this->photo1)->create();

		$response = $this->actingAs($this->admin)->getJsonV3('Faces/queue', ['cluster_label' => 7]);
		$this->assertOk($response);
		self::assertSame([$in->id], $response->json('ids'));
	}

	public function testInvalidClusterFilterIsRejected(): void
	{
		$this->assertUnprocessable($this->actingAs($this->admin)->getJsonV3('Faces/queue', ['cluster_label' => -1]));
		$this->assertUnprocessable($this->actingAs($this->admin)->getJsonV3('Faces/queue', ['cluster_label' => 'abc']));
	}

	public function testUserOnlySeesFacesOnOwnPhotos(): void
	{
		$own = Face::factory()->for_photo($this->photo1)->create();
		$own_unsorted = Face::factory()->for_photo($this->photoUnsorted)->create();
		$other = Face::factory()->for_photo($this->photo2)->create();

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Faces/queue');
		$this->assertOk($response);
		$ids = $response->json('ids');
		self::assertContains($own->id, $ids);
		self::assertContains($own_unsorted->id, $ids);
		self::assertNotContains($other->id, $ids);

		$response = $this->actingAs($this->admin)->getJsonV3('Faces/queue');
		self::assertContains($other->id, $response->json('ids'));
	}

	public function testCapAndTruncation(): void
	{
		Configs::set('ai_vision_face_queue_limit', '2');
		Face::factory()->for_photo($this->photo1)->count(3)->create();

		$response = $this->actingAs($this->admin)->getJsonV3('Faces/queue');
		$this->assertOk($response);
		self::assertCount(2, $response->json('ids'));
		self::assertTrue($response->json('is_truncated'));

		Configs::set('ai_vision_face_queue_limit', '3');
		$response = $this->actingAs($this->admin)->getJsonV3('Faces/queue');
		self::assertCount(3, $response->json('ids'));
		self::assertFalse($response->json('is_truncated'));
	}

	// ── Refetch window (Q-093-14) ──────────────────────────────

	public function testSeenWidensTheWindow(): void
	{
		Configs::set('ai_vision_face_queue_limit', '2');
		Face::factory()->for_photo($this->photo1)->count(3)->create();

		$response = $this->actingAs($this->admin)->getJsonV3('Faces/queue?seen=1');
		$this->assertOk($response);
		self::assertCount(3, $response->json('ids'));
		self::assertFalse($response->json('is_truncated'));
	}

	public function testSkippedBatchStillReachesFacesPastTheCap(): void
	{
		Configs::set('ai_vision_face_queue_limit', '2');
		Face::factory()->for_photo($this->photo1)->count(5)->create();

		$first = $this->actingAs($this->admin)->getJsonV3('Faces/queue');
		$this->assertOk($first);
		$skipped = $first->json('ids');
		self::assertTrue($first->json('is_truncated'));

		// Both faces skipped: they stay in the queue, the refetch reports them as seen.
		$refetch = $this->actingAs($this->admin)->getJsonV3('Faces/queue?seen=' . count($skipped));
		$this->assertOk($refetch);
		$fresh = array_values(array_diff($refetch->json('ids'), $skipped));
		self::assertCount(2, $fresh);
		self::assertTrue($refetch->json('is_truncated'));
	}

	public function testSeenIsNotClamped(): void
	{
		// More still-queued faces than 10 × limit: the window must still reach past them (Q-093-15).
		Configs::set('ai_vision_face_queue_limit', '1');
		Face::factory()->for_photo($this->photo1)->count(13)->create();

		$response = $this->actingAs($this->admin)->getJsonV3('Faces/queue?seen=12');
		$this->assertOk($response);
		self::assertCount(13, $response->json('ids'));
		self::assertFalse($response->json('is_truncated'));
	}

	public function testInvalidSeenIsRejected(): void
	{
		$this->assertUnprocessable($this->actingAs($this->admin)->getJsonV3('Faces/queue?seen=-1'));
		$this->assertUnprocessable($this->actingAs($this->admin)->getJsonV3('Faces/queue?seen=abc'));
	}

	// ── Suggestions (FR-093-12) ─────────────────────────────────

	public function testSuggestionPicksHighestConfidenceAssignedFace(): void
	{
		$alice = Person::factory()->with_name('Alice')->create();
		$bob = Person::factory()->with_name('Bob')->create();
		$alice_face = Face::factory()->for_photo($this->photo1)->for_person($alice)->create();
		$bob_face = Face::factory()->for_photo($this->photo1)->for_person($bob)->create();
		$unassigned = Face::factory()->for_photo($this->photo1b)->create();
		$face = Face::factory()->for_photo($this->photo1)->create();

		DB::table('face_suggestions')->insert([
			['face_id' => $face->id, 'suggested_face_id' => $unassigned->id, 'confidence' => 0.99],
			['face_id' => $face->id, 'suggested_face_id' => $bob_face->id, 'confidence' => 0.7],
			['face_id' => $face->id, 'suggested_face_id' => $alice_face->id, 'confidence' => 0.9],
		]);

		$response = $this->actingAs($this->admin)->getJsonV3('Faces/queue');
		$this->assertOk($response);
		$i = array_search($face->id, $response->json('ids'), true);
		self::assertSame($alice->id, $response->json('suggested_person_ids')[$i]);
		self::assertSame('Alice', $response->json('suggested_person_names')[$i]);

		$j = array_search($unassigned->id, $response->json('ids'), true);
		self::assertNull($response->json('suggested_person_ids')[$j]);
	}

	public function testSuggestionHidesNonSearchablePersonFromUser(): void
	{
		$hidden = Person::factory()->with_name('Hidden')->not_searchable()->create();
		$hidden_face = Face::factory()->for_photo($this->photo2)->for_person($hidden)->create();
		$face = Face::factory()->for_photo($this->photo1)->create();
		DB::table('face_suggestions')->insert(['face_id' => $face->id, 'suggested_face_id' => $hidden_face->id, 'confidence' => 0.9]);

		$response = $this->actingAs($this->userMayUpload1)->getJsonV3('Faces/queue');
		$this->assertOk($response);
		self::assertSame([$face->id], $response->json('ids'));
		self::assertSame([null], $response->json('suggested_person_ids'));
		self::assertSame([null], $response->json('suggested_person_names'));

		$response = $this->actingAs($this->admin)->getJsonV3('Faces/queue');
		$i = array_search($face->id, $response->json('ids'), true);
		self::assertSame($hidden->id, $response->json('suggested_person_ids')[$i]);
	}

	public function testSingleQuery(): void
	{
		Face::factory()->for_photo($this->photo1)->with_cluster(1)->count(2)->create();
		$this->actingAs($this->admin);
		$count = 0;
		DB::listen(function ($query) use (&$count): void {
			if (str_contains($query->sql, '"faces"') || str_contains($query->sql, '`faces`')) {
				$count++;
			}
		});

		$this->assertOk($this->getJsonV3('Faces/queue'));
		self::assertSame(1, $count);
	}
}
