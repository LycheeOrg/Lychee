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

namespace Tests\Unit\Middleware;

use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Configs;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\AbstractTestCase;
use Tests\Traits\RequireSE;

/**
 * Tests that the ResolveAlbumSlug middleware correctly translates
 * album slugs to real IDs before request processing.
 */
class ResolveAlbumSlugTest extends AbstractTestCase
{
	use DatabaseTransactions;
	use RequireSE;

	private const API_PREFIX = '/api/v2/';

	private string $slug = 'my-vacation-photos';
	private User $admin;
	private User $userMayUpload1;
	private User $userLocked;
	private Album $album1;
	private Album $subAlbum1;
	private Album $album4;

	protected function setUp(): void
	{
		parent::setUp();

		$this->requireSe();

		$this->admin = User::factory()->may_administrate()->create();
		$this->userMayUpload1 = User::factory()->may_upload()->create();
		$this->userLocked = User::factory()->locked()->create();

		$this->album1 = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();
		$this->subAlbum1 = Album::factory()->children_of($this->album1)->owned_by($this->userMayUpload1)->create();
		$this->album4 = Album::factory()->as_root()->owned_by($this->userLocked)->create();
		AccessPermission::factory()->public()->visible()->for_album($this->album4)->create();

		Configs::set('owner_id', $this->admin->id);

		// Assign a slug to album1
		DB::table('base_albums')
			->where('id', '=', $this->album1->id)
			->update(['slug' => $this->slug]);

		$this->withoutVite();
	}

	/**
	 * @param array<string,mixed> $data
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	private function apiGetJson(string $uri, array $data = []): TestResponse
	{
		return $this->withCredentials()->json('GET', self::API_PREFIX . $uri, $data);
	}

	/**
	 * @param array<string,mixed> $data
	 *
	 * @return TestResponse<\Illuminate\Http\JsonResponse>
	 */
	private function apiPostJson(string $uri, array $data = []): TestResponse
	{
		return $this->withCredentials()->json('POST', self::API_PREFIX . $uri, $data);
	}

	/**
	 * Test that Album::head resolves a slug to the correct album ID.
	 */
	public function testHeadEndpointResolvesSlug(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->apiGetJson('Album::head', ['album_id' => $this->slug]);
		$this->assertOk($response);
		$response->assertJson([
			'resource' => [
				'id' => $this->album1->id,
				'title' => $this->album1->title,
			],
		]);
	}

	/**
	 * Test that regular 24-char IDs still work (passthrough).
	 */
	public function testHeadEndpointPassesThroughRealId(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->apiGetJson('Album::head', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'resource' => [
				'id' => $this->album1->id,
			],
		]);
	}

	/**
	 * Test that SmartAlbumType values pass through without DB queries.
	 */
	public function testSmartAlbumTypePassthrough(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->apiGetJson('Album::head', ['album_id' => 'unsorted']);
		$this->assertOk($response);
		$response->assertJson([
			'resource' => [
				'id' => 'unsorted',
			],
		]);
	}

	/**
	 * Test that a nonexistent slug results in an unprocessable response.
	 */
	public function testNonexistentSlugReturnsError(): void
	{
		$response = $this->actingAs($this->admin)
			->apiGetJson('Album::head', ['album_id' => 'nonexistent-slug']);
		$this->assertUnprocessable($response);
	}

	/**
	 * Test that Album::albums resolves a slug.
	 */
	public function testAlbumsEndpointResolvesSlug(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->apiGetJson('Album::albums', ['album_id' => $this->slug]);
		$this->assertOk($response);
	}

	/**
	 * Test that Album::photos resolves a slug.
	 */
	public function testPhotosEndpointResolvesSlug(): void
	{
		$response = $this->actingAs($this->userMayUpload1)
			->apiGetJson('Album::photos', ['album_id' => $this->slug]);
		$this->assertOk($response);
	}

	/**
	 * Test authorization still works with slug (private album, anonymous user).
	 */
	public function testSlugResolutionRespectsAuth(): void
	{
		$response = $this->apiGetJson('Album::head', ['album_id' => $this->slug]);
		$this->assertUnauthorized($response);
	}

	/**
	 * Test that slug for public album works for anonymous user.
	 */
	public function testSlugWithPublicAlbum(): void
	{
		$public_slug = 'public-gallery';
		DB::table('base_albums')
			->where('id', '=', $this->album4->id)
			->update(['slug' => $public_slug]);

		$response = $this->apiGetJson('Album::head', ['album_id' => $public_slug]);
		$this->assertOk($response);
		$response->assertJson([
			'resource' => [
				'id' => $this->album4->id,
			],
		]);
	}

	/**
	 * Test batch endpoint (album_ids array) resolves slugs via move.
	 */
	public function testMoveEndpointResolvesSlugArray(): void
	{
		$sub_slug = 'sub-album-slug';
		DB::table('base_albums')
			->where('id', '=', $this->subAlbum1->id)
			->update(['slug' => $sub_slug]);

		// Move subAlbum1 to album1 (it's already there) using slug in album_ids
		$response = $this->actingAs($this->userMayUpload1)
			->apiPostJson('Album::move', [
				'album_id' => $this->album1->id,
				'album_ids' => [$sub_slug],
			]);
		$this->assertNoContent($response);
	}
}
