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
use App\DTO\Profiling\PprofRenderResult;
use App\Models\Configs;
use App\Models\User;
use App\Services\Profiling\PprofRenderer;
use Illuminate\Support\Facades\Storage;
use Tests\Feature_v2\Base\BaseApiTest;

/**
 * Fake renderer so these tests never require the real `pprof`/`google-pprof`
 * CLI or Graphviz to be installed (see spec.md NFR-053-05).
 */
class FakePprofRenderer extends PprofRenderer
{
	public int $render_calls = 0;
	public ?PprofRenderResult $next_result = null;

	public function render(string $pprof_absolute_path): PprofRenderResult
	{
		$this->render_calls++;

		return $this->next_result ?? PprofRenderResult::success('<svg>fake</svg>');
	}
}

class ProfilerSvgTest extends BaseApiTest
{
	private User $owner;

	protected function setUp(): void
	{
		parent::setUp();

		$this->owner = User::factory()->may_administrate()->create();
		Configs::set('owner_id', $this->owner->id);
		config(['features.memory-profiler' => true]);

		foreach (Storage::disk(FileSystem::PROFILING)->allFiles() as $file) {
			Storage::disk(FileSystem::PROFILING)->delete($file);
		}

		Storage::disk(FileSystem::PROFILING)->put('trace-abc123.pprof', 'fake-pprof-content');
		Storage::disk(FileSystem::PROFILING)->put('trace-abc123.json', json_encode([
			'route_name' => 'gallery.index',
			'method' => 'GET',
			'path' => 'gallery',
			'status_code' => 200,
			'duration_ms' => 12.3,
			'peak_memory_bytes' => 1024,
			'user_id' => $this->owner->id,
			'created_at' => '2026-07-28T10:14:02+00:00',
		]));
	}

	protected function tearDown(): void
	{
		foreach (Storage::disk(FileSystem::PROFILING)->allFiles() as $file) {
			Storage::disk(FileSystem::PROFILING)->delete($file);
		}
		parent::tearDown();
	}

	public function testSvgRenderSuccess(): void
	{
		$fake = new FakePprofRenderer();
		$this->app->instance(PprofRenderer::class, $fake);

		$response = $this->actingAs($this->owner)->get('/admin/profiler/trace-abc123/svg');
		$this->assertOk($response);
		$response->assertSee('<svg>fake</svg>', false);
		self::assertSame(1, $fake->render_calls);
		self::assertTrue(Storage::disk(FileSystem::PROFILING)->exists('trace-abc123.svg'));
	}

	public function testSvgIsCachedAfterFirstRender(): void
	{
		$fake = new FakePprofRenderer();
		$this->app->instance(PprofRenderer::class, $fake);

		$this->actingAs($this->owner)->get('/admin/profiler/trace-abc123/svg');
		$this->actingAs($this->owner)->get('/admin/profiler/trace-abc123/svg');

		self::assertSame(1, $fake->render_calls);
	}

	public function testSvgRenderMissingBinaryShowsErrorState(): void
	{
		$fake = new FakePprofRenderer();
		$fake->next_result = PprofRenderResult::binaryMissing('google-pprof');
		$this->app->instance(PprofRenderer::class, $fake);

		$response = $this->actingAs($this->owner)->get('/admin/profiler/trace-abc123/svg');
		$this->assertOk($response);
		$response->assertSee('Could not render this trace');
		self::assertFalse(Storage::disk(FileSystem::PROFILING)->exists('trace-abc123.svg'));
	}

	public function testPathTraversalPayloadReturns404(): void
	{
		$response = $this->actingAs($this->owner)->get('/admin/profiler/' . urlencode('../../.env') . '/svg');
		$this->assertNotFound($response);
	}

	public function testUnknownTraceReturns404(): void
	{
		$response = $this->actingAs($this->owner)->get('/admin/profiler/does-not-exist/svg');
		$this->assertNotFound($response);
	}

	public function testDownloadReturnsRawDump(): void
	{
		$response = $this->actingAs($this->owner)->get('/admin/profiler/trace-abc123/download');
		$this->assertOk($response);
		self::assertSame('fake-pprof-content', $response->streamedContent());
	}
}
