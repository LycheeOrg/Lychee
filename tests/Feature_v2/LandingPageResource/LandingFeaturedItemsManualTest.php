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

use App\Models\Configs;
use App\Models\LandingFeaturedItem;
use App\Models\Photo;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequireSE;
use Tests\Traits\RequiresEmptyLandingFeaturedItems;

/**
 * FR-054-27 / S-054-26..28: manual mode shows exactly the curated, enabled
 * items in order, mixing photos and albums freely, resolved WITHOUT a
 * visibility-policy check (admin-trusted, mirrors Feature 025's photo_id
 * background mode) — this bypass is intentional, not a privacy bug.
 */
class LandingFeaturedItemsManualTest extends BaseApiWithDataTest
{
	use RequireSE;
	use RequiresEmptyLandingFeaturedItems;

	private array $original = [];

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyLandingFeaturedItems();
		foreach (['landing_featured_items_enabled', 'landing_featured_items_mode'] as $key) {
			$this->original[$key] = Configs::query()->where('key', '=', $key)->value('value');
		}
		Configs::set('landing_featured_items_enabled', '1');
		Configs::set('landing_featured_items_mode', 'manual');
		$this->requireSe();

		// This fixture album/photo graph is not granted public access; it is
		// exactly the "private" content manual mode is trusted to surface.
		$this->perm4->delete();
		$this->perm44->delete();
	}

	public function tearDown(): void
	{
		foreach ($this->original as $key => $value) {
			if ($value !== null) {
				Configs::set($key, $value);
			}
		}
		$this->resetSe();
		$this->tearDownRequiresEmptyLandingFeaturedItems();
		parent::tearDown();
	}

	public function testShowsOnlyEnabledItemsMixedTypesInOrder(): void
	{
		$photo = Photo::factory()->owned_by($this->admin)->in($this->album4)->create();

		LandingFeaturedItem::factory()->album($this->album4->id)->create(['sort_order' => 1, 'enabled' => true]);
		LandingFeaturedItem::factory()->photo($photo->id)->create(['sort_order' => 0, 'enabled' => true]);
		LandingFeaturedItem::factory()->album($this->subAlbum4->id)->create(['sort_order' => 2, 'enabled' => false]);

		$response = $this->getJson('LandingPage');
		$this->assertOk($response);
		$response->assertJsonCount(2, 'featured_items');
		$response->assertJsonPath('featured_items.0.item_type', 'photo');
		$response->assertJsonPath('featured_items.0.id', $photo->id);
		$response->assertJsonPath('featured_items.1.item_type', 'album');
		$response->assertJsonPath('featured_items.1.id', $this->album4->id);
	}

	public function testSilentlySkipsADeletedReferencedAlbum(): void
	{
		$deletable = $this->album5;
		LandingFeaturedItem::factory()->album($deletable->id)->create(['sort_order' => 0, 'enabled' => true]);
		LandingFeaturedItem::factory()->album($this->album4->id)->create(['sort_order' => 1, 'enabled' => true]);

		$deletable->forceDelete();

		$response = $this->getJson('LandingPage');
		$this->assertOk($response);
		$response->assertJsonCount(1, 'featured_items');
		$response->assertJsonPath('featured_items.0.id', $this->album4->id);
	}

	public function testSectionIsEmptyWithZeroEnabledItems(): void
	{
		LandingFeaturedItem::factory()->album($this->album4->id)->create(['enabled' => false]);

		$response = $this->getJson('LandingPage');
		$this->assertOk($response);
		$response->assertJsonPath('featured_items', []);
	}

	/**
	 * This is the deliberate admin-trusted exception (mirrors Feature 025's
	 * photo_id background mode): a manually curated private album resolves
	 * successfully even though it has no public AccessPermission grant. This
	 * must NOT be "fixed" later as a privacy bug — see FR-054-27/NFR-054-03.
	 */
	public function testResolvesAManuallyCuratedPrivateAlbumByDesign(): void
	{
		// album4 was stripped of its public grant in setUp(): it is private.
		LandingFeaturedItem::factory()->album($this->album4->id)->create(['enabled' => true]);

		$response = $this->getJson('LandingPage');
		$this->assertOk($response);
		$response->assertJsonCount(1, 'featured_items');
		$response->assertJsonPath('featured_items.0.id', $this->album4->id);
	}
}
