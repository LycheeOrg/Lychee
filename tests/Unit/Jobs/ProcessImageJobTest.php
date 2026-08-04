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

use App\Enum\UserUploadTrustLevel;
use App\Exceptions\OwnerRequiredException;
use App\Image\Files\ProcessableJobFile;
use App\Jobs\ProcessImageJob;
use App\Models\Album;
use App\Models\JobHistory;
use App\Models\User;
use App\Repositories\ConfigManager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Mockery\MockInterface;
use Tests\AbstractTestCase;

/**
 * Only the constructor (album/owner/trust-level resolution) is covered here.
 * `handle()` delegates to `App\Actions\Photo\Create`, which is instantiated
 * directly (not injected) and performs real image processing, so it is not
 * unit-testable without exercising the full image pipeline.
 */
class ProcessImageJobTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private function makeFile(): ProcessableJobFile
	{
		$file = \Mockery::mock(ProcessableJobFile::class);
		$file->shouldReceive('getPath')->andReturn('/tmp/some-file.jpg');
		$file->shouldReceive('getOriginalBasename')->andReturn('some-file.jpg');

		return $file;
	}

	private function mockConfigManager(bool $watermark_optout_disabled = false): void
	{
		$this->mock(ConfigManager::class, function (MockInterface $mock) use ($watermark_optout_disabled): void {
			$mock->shouldReceive('getValueAsBool')->with('watermark_optout_disabled')->andReturn($watermark_optout_disabled);
			$mock->shouldReceive('getValueAsEnum')->with('guest_upload_trust_level', UserUploadTrustLevel::class)->andReturn(UserUploadTrustLevel::CHECK);
		});
	}

	public function testThrowsWhenNoUserAndNoAlbum(): void
	{
		Auth::shouldReceive('user')->once()->andReturn(null);
		$this->mockConfigManager();

		$this->assertThrows(
			fn () => new ProcessImageJob($this->makeFile(), null, null),
			OwnerRequiredException::class
		);
	}

	public function testUsesAlbumOwnerWhenGuestUploadsToRealAlbum(): void
	{
		Auth::shouldReceive('user')->atLeast()->once()->andReturn(null);
		$this->mockConfigManager();

		$owner = User::factory()->create();
		$album = Album::factory()->as_root()->owned_by($owner)->create();

		$job = new ProcessImageJob($this->makeFile(), $album, null);

		self::assertEquals($owner->id, $job->user_id);
		self::assertEquals($album->id, $job->album_id);
		self::assertEquals(UserUploadTrustLevel::CHECK, $job->upload_trust_level);

		$history = JobHistory::query()->where('owner_id', '=', $owner->id)->latest()->first();
		self::assertNotNull($history);
	}

	public function testAdministratorAlwaysGetsTrustedLevel(): void
	{
		$admin = User::factory()->may_administrate()->create(['upload_trust_level' => UserUploadTrustLevel::CHECK]);
		Auth::shouldReceive('user')->once()->andReturn($admin);
		$this->mockConfigManager();

		$job = new ProcessImageJob($this->makeFile(), null, null);

		self::assertEquals(UserUploadTrustLevel::TRUSTED, $job->upload_trust_level);
		self::assertEquals($admin->id, $job->user_id);
	}

	public function testRegularUserUsesTheirOwnTrustLevel(): void
	{
		$user = User::factory()->create(['upload_trust_level' => UserUploadTrustLevel::MONITOR]);
		Auth::shouldReceive('user')->once()->andReturn($user);
		$this->mockConfigManager();

		$job = new ProcessImageJob($this->makeFile(), null, null);

		self::assertEquals(UserUploadTrustLevel::MONITOR, $job->upload_trust_level);
	}

	public function testWatermarkOptOutDisabledForcesNullApplyWatermark(): void
	{
		$user = User::factory()->create();
		Auth::shouldReceive('user')->once()->andReturn($user);
		$this->mockConfigManager(watermark_optout_disabled: true);

		$job = new ProcessImageJob($this->makeFile(), null, null, apply_watermark: true);

		self::assertNull($job->apply_watermark);
	}

	public function testAppliesWatermarkPreferenceWhenOptOutAllowed(): void
	{
		$user = User::factory()->create();
		Auth::shouldReceive('user')->once()->andReturn($user);
		$this->mockConfigManager(watermark_optout_disabled: false);

		$job = new ProcessImageJob($this->makeFile(), null, null, apply_watermark: false);

		self::assertFalse($job->apply_watermark);
	}
}
