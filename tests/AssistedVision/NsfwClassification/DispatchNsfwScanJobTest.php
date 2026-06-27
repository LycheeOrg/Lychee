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

namespace Tests\AssistedVision\NsfwClassification;

use App\Enum\NsfwStatus;
use App\Jobs\DispatchNsfwScanJob;
use App\Models\Photo;
use App\Models\SizeVariant;
use App\Models\User;
use App\Services\Image\NsfwDetectionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Log;
use Tests\AbstractTestCase;

class DispatchNsfwScanJobTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private User $user;

	protected function setUp(): void
	{
		parent::setUp();
		$this->user = User::factory()->may_administrate()->create();
	}

	public function testSkipsWhenPhotoNotFound(): void
	{
		$service = $this->createMock(NsfwDetectionService::class);
		$service->expects(self::never())->method('dispatchPhoto');

		Log::shouldReceive('warning')
			->once()
			->withArgs(fn (string $msg) => str_contains($msg, 'not found'));

		$job = new DispatchNsfwScanJob('nonexistent-photo-id');
		$job->handle($service);
	}

	public function testMarksFailedWhenServiceNotConfigured(): void
	{
		Log::shouldReceive('warning')->once();

		$photo = Photo::factory()->owned_by($this->user)->create();

		$service = $this->createMock(NsfwDetectionService::class);
		$service->method('isConfigured')->willReturn(false);
		$service->expects(self::never())->method('dispatchPhoto');

		$job = new DispatchNsfwScanJob($photo->id);
		$job->handle($service);

		$photo->refresh();
		self::assertEquals(NsfwStatus::FAILED, $photo->nsfw_status);
	}

	public function testMarksFailedWhenNoOriginalSizeVariant(): void
	{
		Log::shouldReceive('warning')->once();

		$photo = Photo::factory()->owned_by($this->user)->create();
		SizeVariant::where('photo_id', $photo->id)->delete();

		$service = $this->createMock(NsfwDetectionService::class);
		$service->method('isConfigured')->willReturn(true);
		$service->expects(self::never())->method('dispatchPhoto');

		$job = new DispatchNsfwScanJob($photo->id);
		$job->handle($service);

		$photo->refresh();
		self::assertEquals(NsfwStatus::FAILED, $photo->nsfw_status);
	}

	public function testMarksFailedWhenServiceReturnsError(): void
	{
		Log::shouldReceive('warning')->once();
		Log::shouldReceive('info')->zeroOrMoreTimes();

		$photo = Photo::factory()->owned_by($this->user)->create();

		$response = $this->createMock(Response::class);
		$response->method('successful')->willReturn(false);
		$response->method('status')->willReturn(500);
		$response->method('json')->willReturn(['error' => 'internal']);

		$service = $this->createMock(NsfwDetectionService::class);
		$service->method('isConfigured')->willReturn(true);
		$service->method('dispatchPhoto')->willReturn($response);

		$job = new DispatchNsfwScanJob($photo->id);
		$job->handle($service);

		$photo->refresh();
		self::assertEquals(NsfwStatus::FAILED, $photo->nsfw_status);
	}

	public function testMarksFailedWhenServiceThrowsException(): void
	{
		Log::shouldReceive('warning')->once();

		$photo = Photo::factory()->owned_by($this->user)->create();

		$service = $this->createMock(NsfwDetectionService::class);
		$service->method('isConfigured')->willReturn(true);
		$service->method('dispatchPhoto')->willThrowException(new \RuntimeException('Connection refused'));

		$job = new DispatchNsfwScanJob($photo->id);
		$job->handle($service);

		$photo->refresh();
		self::assertEquals(NsfwStatus::FAILED, $photo->nsfw_status);
	}

	public function testSetsPendingOnSuccess(): void
	{
		$photo = Photo::factory()->owned_by($this->user)->create();

		$response = $this->createMock(Response::class);
		$response->method('successful')->willReturn(true);

		$service = $this->createMock(NsfwDetectionService::class);
		$service->method('isConfigured')->willReturn(true);
		$service->method('dispatchPhoto')->willReturn($response);

		$job = new DispatchNsfwScanJob($photo->id);
		$job->handle($service);

		$photo->refresh();
		self::assertEquals(NsfwStatus::PENDING, $photo->nsfw_status);
	}

	public function testFailedMethodMarksPhotoAsFailed(): void
	{
		Log::shouldReceive('error')->once();

		$photo = Photo::factory()->owned_by($this->user)->create();
		Photo::where('id', $photo->id)->update(['nsfw_status' => NsfwStatus::PENDING->value]);

		$job = new DispatchNsfwScanJob($photo->id);
		$job->failed(new \RuntimeException('Queue exhausted retries'));

		$photo->refresh();
		self::assertEquals(NsfwStatus::FAILED, $photo->nsfw_status);
	}
}
