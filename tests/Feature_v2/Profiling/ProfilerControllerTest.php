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

namespace Tests\Feature_v2\Profiling;

use App\Constants\FileSystem;
use App\Models\Configs;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\Feature_v2\Base\BaseApiTest;

class ProfilerControllerTest extends BaseApiTest
{
	private User $owner;
	private User $other;

	protected function setUp(): void
	{
		parent::setUp();

		$this->owner = User::factory()->may_administrate()->create();
		$this->other = User::factory()->may_administrate()->create();
		Configs::set('owner_id', $this->owner->id);

		foreach (Storage::disk(FileSystem::PROFILING)->allFiles() as $file) {
			Storage::disk(FileSystem::PROFILING)->delete($file);
		}
	}

	protected function tearDown(): void
	{
		foreach (Storage::disk(FileSystem::PROFILING)->allFiles() as $file) {
			Storage::disk(FileSystem::PROFILING)->delete($file);
		}
		parent::tearDown();
	}

	public function testFeatureDisabledReturns501(): void
	{
		config(['features.memory-profiler' => false]);

		$response = $this->actingAs($this->owner)->get('/admin/profiler');
		$this->assertStatus($response, 501);
	}

	public function testUnauthenticatedIsRedirected(): void
	{
		config(['features.memory-profiler' => true]);

		$response = $this->get('/admin/profiler');
		$this->assertRedirect($response);
	}

	public function testNonOwnerIsForbidden(): void
	{
		config(['features.memory-profiler' => true]);

		$response = $this->actingAs($this->other)->get('/admin/profiler');
		$this->assertForbidden($response);
	}

	public function testOwnerSeesEmptyState(): void
	{
		config(['features.memory-profiler' => true]);

		$response = $this->actingAs($this->owner)->get('/admin/profiler');
		$this->assertOk($response);
		$response->assertSee('No traces collected yet');
	}

	public function testOwnerSeesPopulatedListingWithSpxLink(): void
	{
		config([
			'features.memory-profiler' => true,
			'features.memory-profiler-spx-key' => 'test-secret-key',
		]);

		Storage::disk(FileSystem::PROFILING)->put('lychee-20260728_101402_abc12345.json', json_encode([
			'spx_report_key' => 'spx-full-20260728_101402-host-123-456',
			'route_name' => 'gallery.index',
			'method' => 'GET',
			'path' => 'gallery',
			'status_code' => 200,
			'duration_ms' => 12.3,
			'peak_memory_bytes' => 1024,
			'user_id' => $this->owner->id,
			'created_at' => '2026-07-28T10:14:02+00:00',
		]));

		$response = $this->actingAs($this->owner)->get('/admin/profiler');
		$this->assertOk($response);
		$response->assertSee('gallery.index');
		$response->assertSee('200');
		$response->assertSee('SPX_KEY=test-secret-key', false);
		$response->assertSee('key=spx-full-20260728_101402-host-123-456', false);
	}

	public function testOwnerSeesPopulatedListingWithoutSpxKeyConfigured(): void
	{
		config([
			'features.memory-profiler' => true,
			'features.memory-profiler-spx-key' => null,
		]);

		Storage::disk(FileSystem::PROFILING)->put('lychee-20260728_101402_abc12345.json', json_encode([
			'spx_report_key' => 'spx-full-20260728_101402-host-123-456',
			'route_name' => 'gallery.index',
			'method' => 'GET',
			'path' => 'gallery',
			'status_code' => 200,
			'duration_ms' => 12.3,
			'peak_memory_bytes' => 1024,
			'user_id' => $this->owner->id,
			'created_at' => '2026-07-28T10:14:02+00:00',
		]));

		$response = $this->actingAs($this->owner)->get('/admin/profiler');
		$this->assertOk($response);
		$response->assertSee('gallery.index');
		$response->assertDontSee('SPX_KEY=test-secret-key', false);
	}

	public function testPruneRedirectsToIndex(): void
	{
		config(['features.memory-profiler' => true, 'features.memory-profiler-max-traces' => 0]);

		Storage::disk(FileSystem::PROFILING)->put('lychee-old.json', json_encode([
			'spx_report_key' => 'spx-full-old',
			'route_name' => null,
			'method' => 'GET',
			'path' => 'foo',
			'status_code' => 200,
			'duration_ms' => 1.0,
			'peak_memory_bytes' => 1,
			'user_id' => null,
			'created_at' => '2026-07-28T10:00:00+00:00',
		]));
		Storage::disk(FileSystem::PROFILING)->put('spx-full-old.json', '{}');
		Storage::disk(FileSystem::PROFILING)->put('spx-full-old.txt.gz', 'content');

		$response = $this->actingAs($this->owner)->post('/admin/profiler/prune');
		$this->assertRedirect($response);
		self::assertFalse(Storage::disk(FileSystem::PROFILING)->exists('lychee-old.json'));
		self::assertFalse(Storage::disk(FileSystem::PROFILING)->exists('spx-full-old.json'));
	}
}
