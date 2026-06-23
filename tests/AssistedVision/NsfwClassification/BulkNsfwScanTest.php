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

use App\Jobs\DispatchNsfwScanJob;
use App\Models\Configs;
use Illuminate\Support\Facades\Queue;
use LycheeVerify\Http\Middleware\VerifySupporterStatus;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class BulkNsfwScanTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_nsfw_enabled', '1');
	}

	public function testBulkScanAsAdminDispatches(): void
	{
		Queue::fake();

		$response = $this->withoutMiddleware(VerifySupporterStatus::class)
			->actingAs($this->admin)->postJson('NsfwDetection/bulk-scan');
		$this->assertNoContent($response);

		Queue::assertPushed(DispatchNsfwScanJob::class);
	}

	public function testBulkScanAsUserForbidden(): void
	{
		$response = $this->withoutMiddleware(VerifySupporterStatus::class)
			->actingAs($this->userMayUpload1)->postJson('NsfwDetection/bulk-scan');
		$this->assertForbidden($response);
	}

	public function testBulkScanAsGuestUnauthorized(): void
	{
		$response = $this->withoutMiddleware(VerifySupporterStatus::class)
			->postJson('NsfwDetection/bulk-scan');
		$this->assertUnauthorized($response);
	}

	public function testBulkScanWithAlbumId(): void
	{
		Queue::fake();

		$response = $this->withoutMiddleware(VerifySupporterStatus::class)
			->actingAs($this->admin)->postJson('NsfwDetection/bulk-scan', [
				'album_id' => $this->album1->id,
			]);
		$this->assertNoContent($response);
	}

	public function testBulkScanWithForceFlag(): void
	{
		Queue::fake();

		$response = $this->withoutMiddleware(VerifySupporterStatus::class)
			->actingAs($this->admin)->postJson('NsfwDetection/bulk-scan', [
				'force' => true,
			]);
		$this->assertNoContent($response);
	}
}
