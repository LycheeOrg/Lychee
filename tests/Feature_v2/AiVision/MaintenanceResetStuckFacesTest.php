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

class MaintenanceResetStuckFacesTest extends BaseApiWithDataTest
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
		$response = $this->getJson('Maintenance::resetStuckFaces');
		$this->assertUnauthorized($response);
	}

	public function testCheckAsUser(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('Maintenance::resetStuckFaces');
		$this->assertForbidden($response);
	}

	public function testCheckAsAdmin(): void
	{
		// Create a stuck pending photo
		Photo::where('id', $this->photo1->id)->update([
			'face_scan_status' => FaceScanStatus::PENDING,
			'updated_at' => Carbon::now()->subMinutes(800),
		]);

		$response = $this->actingAs($this->admin)->getJson('Maintenance::resetStuckFaces');
		$this->assertOk($response);

		$count = $response->json();
		self::assertGreaterThanOrEqual(1, $count);
	}

	public function testCheckReturnsZeroWhenNoneStuck(): void
	{
		$response = $this->actingAs($this->admin)->getJson('Maintenance::resetStuckFaces');
		$this->assertOk($response);
		self::assertEquals(0, $response->json());
	}

	// ── DO (POST) ───────────────────────────────────────────────

	public function testDoAsGuest(): void
	{
		$response = $this->postJson('Maintenance::resetStuckFaces');
		$this->assertUnauthorized($response);
	}

	public function testDoAsUser(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Maintenance::resetStuckFaces');
		$this->assertForbidden($response);
	}

	public function testDoAsAdmin(): void
	{
		// Create a stuck pending photo (older than default threshold)
		Photo::where('id', $this->photo1->id)->update([
			'face_scan_status' => FaceScanStatus::PENDING,
			'updated_at' => Carbon::now()->subMinutes(800),
		]);

		$response = $this->actingAs($this->admin)->postJson('Maintenance::resetStuckFaces');
		$this->assertOk($response);
		self::assertGreaterThanOrEqual(1, $response->json('reset_count'));

		// Photo should be reset to null
		$this->photo1->refresh();
		self::assertNull($this->photo1->face_scan_status);
	}

	public function testDoWithCustomThreshold(): void
	{
		// Set pending photo that is 30 minutes old
		Photo::where('id', $this->photo1->id)->update([
			'face_scan_status' => FaceScanStatus::PENDING,
			'updated_at' => Carbon::now()->subMinutes(30),
		]);

		// Default threshold is 720 min (12h), so this should NOT be reset
		$response = $this->actingAs($this->admin)->postJson('Maintenance::resetStuckFaces');
		$this->assertOk($response);
		self::assertEquals(0, $response->json('reset_count'));

		// With a custom threshold of 15 minutes, it SHOULD be reset
		$response = $this->actingAs($this->admin)->postJson('Maintenance::resetStuckFaces', [
			'older_than_minutes' => 15,
		]);
		$this->assertOk($response);
		self::assertGreaterThanOrEqual(1, $response->json('reset_count'));
	}

	public function testDoDoesNotResetRecentPending(): void
	{
		// Set pending photo that is recent (5 minutes)
		Photo::where('id', $this->photo1->id)->update([
			'face_scan_status' => FaceScanStatus::PENDING,
			'updated_at' => Carbon::now()->subMinutes(5),
		]);

		$response = $this->actingAs($this->admin)->postJson('Maintenance::resetStuckFaces', [
			'older_than_minutes' => 60,
		]);
		$this->assertOk($response);
		self::assertEquals(0, $response->json('reset_count'));

		// Photo should still be pending
		$this->photo1->refresh();
		self::assertEquals(FaceScanStatus::PENDING, $this->photo1->face_scan_status);
	}
}
