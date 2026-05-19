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

namespace Tests\Feature_v2\AiVision;

use App\Enum\FaceScanStatus;
use App\Models\Photo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class MaintenanceResetFaceScanStatusTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();
	}

	public function tearDown(): void
	{
		DB::table('face_suggestions')->delete();
		DB::table('faces')->delete();
		DB::table('persons')->delete();
		parent::tearDown();
	}

	// ── CHECK (GET) ─────────────────────────────────────────────

	public function testCheckAsGuest(): void
	{
		$response = $this->getJson('Maintenance::resetFaceScanStatus');
		$this->assertUnauthorized($response);
	}

	public function testCheckAsUser(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('Maintenance::resetFaceScanStatus');
		$this->assertForbidden($response);
	}

	public function testCheckAsAdmin(): void
	{
		// Create a stuck pending photo and a failed photo
		Photo::where('id', $this->photo1->id)->update([
			'face_scan_status' => FaceScanStatus::PENDING,
			'updated_at' => Carbon::now()->subMinutes(800),
		]);

		Photo::where('id', $this->photo2->id)->update([
			'face_scan_status' => FaceScanStatus::FAILED,
		]);

		$response = $this->actingAs($this->admin)->getJson('Maintenance::resetFaceScanStatus');
		$this->assertOk($response);

		$count = $response->json();
		self::assertGreaterThanOrEqual(2, $count); // Should count both stuck and failed
	}

	public function testCheckReturnsZeroWhenNoneStuck(): void
	{
		$response = $this->actingAs($this->admin)->getJson('Maintenance::resetFaceScanStatus');
		$this->assertOk($response);
		self::assertEquals(0, $response->json());
	}

	// ── DO (POST) ───────────────────────────────────────────────

	public function testDoAsGuest(): void
	{
		$response = $this->postJson('Maintenance::resetFaceScanStatus');
		$this->assertUnauthorized($response);
	}

	public function testDoAsUser(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Maintenance::resetFaceScanStatus');
		$this->assertForbidden($response);
	}

	public function testDoResetsStuckPending(): void
	{
		// Create a stuck pending photo (older than default threshold of 720 min)
		Photo::where('id', $this->photo1->id)->update([
			'face_scan_status' => FaceScanStatus::PENDING,
			'updated_at' => Carbon::now()->subMinutes(800),
		]);

		$response = $this->actingAs($this->admin)->postJson('Maintenance::resetFaceScanStatus');
		$this->assertOk($response);
		self::assertGreaterThanOrEqual(1, $response->json('reset_count'));

		// Photo should be reset to null
		$this->photo1->refresh();
		self::assertNull($this->photo1->face_scan_status);
	}

	public function testDoResetsFailed(): void
	{
		// Create a failed photo
		Photo::where('id', $this->photo1->id)->update([
			'face_scan_status' => FaceScanStatus::FAILED,
		]);

		$response = $this->actingAs($this->admin)->postJson('Maintenance::resetFaceScanStatus');
		$this->assertOk($response);
		self::assertGreaterThanOrEqual(1, $response->json('reset_count'));

		// Photo should be reset to null
		$this->photo1->refresh();
		self::assertNull($this->photo1->face_scan_status);
	}

	public function testDoResetsBothStuckAndFailed(): void
	{
		// Create stuck pending and failed photos
		Photo::where('id', $this->photo1->id)->update([
			'face_scan_status' => FaceScanStatus::PENDING,
			'updated_at' => Carbon::now()->subMinutes(800),
		]);

		Photo::where('id', $this->photo2->id)->update([
			'face_scan_status' => FaceScanStatus::FAILED,
		]);

		$response = $this->actingAs($this->admin)->postJson('Maintenance::resetFaceScanStatus');
		$this->assertOk($response);
		self::assertGreaterThanOrEqual(2, $response->json('reset_count'));

		// Both photos should be reset to null
		$this->photo1->refresh();
		$this->photo2->refresh();
		self::assertNull($this->photo1->face_scan_status);
		self::assertNull($this->photo2->face_scan_status);
	}

	public function testDoDoesNotResetRecentPending(): void
	{
		// Set pending photo that is recent (5 minutes) - should NOT be reset
		Photo::where('id', $this->photo1->id)->update([
			'face_scan_status' => FaceScanStatus::PENDING,
			'updated_at' => Carbon::now()->subMinutes(5),
		]);

		$response = $this->actingAs($this->admin)->postJson('Maintenance::resetFaceScanStatus');
		$this->assertOk($response);
		self::assertEquals(0, $response->json('reset_count'));

		// Photo should still be pending
		$this->photo1->refresh();
		self::assertEquals(FaceScanStatus::PENDING, $this->photo1->face_scan_status);
	}
}
