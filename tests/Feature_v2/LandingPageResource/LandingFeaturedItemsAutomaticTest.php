<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v2\LandingPageResource;

use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\Configs;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequireSE;

/**
 * FR-054-09 / S-054-12..14 / NFR-054-03: automatic mode exposes up to N
 * recently-published public albums, never leaks private albums, and
 * gracefully handles fewer-than-N and zero-album cases.
 */
class LandingFeaturedItemsAutomaticTest extends BaseApiWithDataTest
{
	use RequireSE;

	private array $original = [];

	public function setUp(): void
	{
		parent::setUp();
		foreach (['landing_featured_items_enabled', 'landing_featured_items_mode', 'landing_featured_items_count'] as $key) {
			$this->original[$key] = Configs::query()->where('key', '=', $key)->value('value');
		}
		Configs::set('landing_featured_items_enabled', '1');
		Configs::set('landing_featured_items_mode', 'automatic');
		$this->requireSe();
	}

	public function tearDown(): void
	{
		foreach ($this->original as $key => $value) {
			if ($value !== null) {
				Configs::set($key, $value);
			}
		}
		$this->resetSe();
		parent::tearDown();
	}

	private function createPublicAlbum(): Album
	{
		$album = Album::factory()->as_root()->owned_by($this->admin)->create();
		AccessPermission::factory()->public()->visible()->for_album($album)->create();

		return $album;
	}

	/**
	 * The BaseApiWithDataTest fixture graph already grants public+visible
	 * access to album4/subAlbum4. Revoke those so a test can rely on a true
	 * zero-public-albums baseline.
	 */
	private function revokeFixturePublicAlbums(): void
	{
		$this->perm4->delete();
		$this->perm44->delete();
	}

	public function testReturnsUpToConfiguredCount(): void
	{
		$this->revokeFixturePublicAlbums();
		Configs::set('landing_featured_items_count', '3');
		for ($i = 0; $i < 5; $i++) {
			$this->createPublicAlbum();
		}

		$response = $this->getJson('LandingPage');
		$this->assertOk($response);
		$response->assertJsonCount(3, 'featured_items');
		$response->assertJsonPath('featured_items.0.item_type', 'album');
	}

	public function testReturnsFewerWhenNotEnoughPublicAlbumsExist(): void
	{
		$this->revokeFixturePublicAlbums();
		Configs::set('landing_featured_items_count', '6');
		$this->createPublicAlbum();
		$this->createPublicAlbum();

		$response = $this->getJson('LandingPage');
		$this->assertOk($response);
		$response->assertJsonCount(2, 'featured_items');
	}

	public function testSectionIsEmptyWhenNoPublicAlbumsExist(): void
	{
		$this->revokeFixturePublicAlbums();

		$response = $this->getJson('LandingPage');
		$this->assertOk($response);
		$response->assertJsonPath('featured_items', []);
	}

	public function testNeverSurfacesAPrivateAlbum(): void
	{
		$this->revokeFixturePublicAlbums();

		// Private: no AccessPermission grant at all.
		$private_album = Album::factory()->as_root()->owned_by($this->admin)->create();
		$public_album = $this->createPublicAlbum();

		$response = $this->getJson('LandingPage');
		$this->assertOk($response);
		$response->assertJsonCount(1, 'featured_items');
		$response->assertJsonPath('featured_items.0.id', $public_album->id);
		$ids = collect($response->json('featured_items'))->pluck('id')->all();
		$this->assertNotContains($private_album->id, $ids);
	}
}
