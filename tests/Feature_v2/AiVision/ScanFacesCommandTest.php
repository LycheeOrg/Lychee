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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class ScanFacesCommandTest extends BaseApiWithDataTest
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

	public function testCommandEnqueuesUnscannedPhotos(): void
	{
		Queue::fake();

		// Ensure photo1 is unscanned (null status)
		Photo::where('id', $this->photo1->id)->update(['face_scan_status' => null]);

		$this->artisan('lychee:scan-faces')
			->expectsOutputToContain('Dispatched')
			->assertExitCode(0);

		// Photo should now be pending
		$this->photo1->refresh();
		self::assertEquals(FaceScanStatus::PENDING, $this->photo1->face_scan_status);
	}

	public function testCommandSkipsAlreadyScanned(): void
	{
		Queue::fake();

		// Mark photo as completed
		Photo::where('id', $this->photo1->id)->update(['face_scan_status' => FaceScanStatus::COMPLETED->value]);

		$this->artisan('lychee:scan-faces')
			->expectsOutputToContain('Dispatched')
			->assertExitCode(0);

		// Should still be completed (not re-queued)
		$this->photo1->refresh();
		self::assertEquals(FaceScanStatus::COMPLETED, $this->photo1->face_scan_status);
	}

	public function testCommandWithAlbumFilter(): void
	{
		Queue::fake();

		// Ensure photos are unscanned
		Photo::where('id', $this->photo1->id)->update(['face_scan_status' => null]);
		Photo::where('id', $this->photo2->id)->update(['face_scan_status' => null]);

		$this->artisan('lychee:scan-faces', ['--album' => $this->album1->id])
			->expectsOutputToContain('Dispatched')
			->assertExitCode(0);

		// photo1 is in album1 — should be pending
		$this->photo1->refresh();
		self::assertEquals(FaceScanStatus::PENDING, $this->photo1->face_scan_status);

		// photo2 is in album2 — should still be null
		$this->photo2->refresh();
		self::assertNull($this->photo2->face_scan_status);
	}
}
