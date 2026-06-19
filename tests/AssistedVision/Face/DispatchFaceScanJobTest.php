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

use App\Enum\FaceScanStatus;
use App\Jobs\DispatchFaceScanJob;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Models\User;
use App\Services\Image\FacialRecognitionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use Tests\AbstractTestCase;

class DispatchFaceScanJobTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private User $user;

	protected function setUp(): void
	{
		parent::setUp();
		$this->user = User::factory()->may_administrate()->create();
	}

	// ── photo not found ─────────────────────────────────────────

	public function testSkipsWhenPhotoNotFound(): void
	{
		$service = $this->createMock(FacialRecognitionService::class);
		$service->expects(self::never())->method('detectFaces');

		Log::shouldReceive('warning')
			->once()
			->withArgs(fn (string $msg) => str_contains($msg, 'not found'));

		$job = new DispatchFaceScanJob('nonexistent-photo-id');
		$job->handle($service);
	}

	// ── service not configured ──────────────────────────────────

	public function testMarksFailedWhenServiceNotConfigured(): void
	{
		$photo = Photo::factory()->owned_by($this->user)->create();

		$service = $this->createMock(FacialRecognitionService::class);
		$service->method('isConfigured')->willReturn(false);
		$service->expects(self::never())->method('detectFaces');

		$job = new DispatchFaceScanJob($photo->id);
		$job->handle($service);

		$photo->refresh();
		self::assertEquals(FaceScanStatus::FAILED, $photo->face_scan_status);
	}

	// ── no original size variant ────────────────────────────────

	public function testMarksFailedWhenNoOriginalSizeVariant(): void
	{
		$photo = Photo::factory()->owned_by($this->user)->create();
		SizeVariant::where('photo_id', $photo->id)->delete();

		$service = $this->createMock(FacialRecognitionService::class);
		$service->method('isConfigured')->willReturn(true);
		$service->expects(self::never())->method('detectFaces');

		$job = new DispatchFaceScanJob($photo->id);
		$job->handle($service);

		$photo->refresh();
		self::assertEquals(FaceScanStatus::FAILED, $photo->face_scan_status);
	}

	// ── detection fails with HTTP error ─────────────────────────

	public function testMarksFailedWhenDetectReturnsError(): void
	{
		$photo = Photo::factory()->owned_by($this->user)->create();

		$response = $this->createMock(Response::class);
		$response->method('successful')->willReturn(false);
		$response->method('status')->willReturn(500);
		$response->method('json')->willReturn(['error' => 'internal']);

		$service = $this->createMock(FacialRecognitionService::class);
		$service->method('isConfigured')->willReturn(true);
		$service->method('detectFaces')->willReturn($response);

		$job = new DispatchFaceScanJob($photo->id);
		$job->handle($service);

		$photo->refresh();
		self::assertEquals(FaceScanStatus::FAILED, $photo->face_scan_status);
	}

	// ── detection throws exception ──────────────────────────────

	public function testMarksFailedWhenDetectThrowsException(): void
	{
		$photo = Photo::factory()->owned_by($this->user)->create();

		$service = $this->createMock(FacialRecognitionService::class);
		$service->method('isConfigured')->willReturn(true);
		$service->method('detectFaces')->willThrowException(new \RuntimeException('Connection refused'));

		$job = new DispatchFaceScanJob($photo->id);
		$job->handle($service);

		$photo->refresh();
		self::assertEquals(FaceScanStatus::FAILED, $photo->face_scan_status);
	}

	// ── detection succeeds ──────────────────────────────────────

	public function testDoesNotMarkFailedOnSuccess(): void
	{
		$photo = Photo::factory()->owned_by($this->user)->create();

		$response = $this->createMock(Response::class);
		$response->method('successful')->willReturn(true);

		$service = $this->createMock(FacialRecognitionService::class);
		$service->method('isConfigured')->willReturn(true);
		$service->method('detectFaces')->willReturn($response);

		$job = new DispatchFaceScanJob($photo->id);
		$job->handle($service);

		$photo->refresh();
		self::assertNotEquals(FaceScanStatus::FAILED, $photo->face_scan_status);
	}
}
