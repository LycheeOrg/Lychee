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

namespace Tests\Feature_v2;

use App\Models\Configs;
use App\Models\Photo;
use App\Repositories\ConfigManager;
use Exception;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class RssTest extends BaseApiWithDataTest
{
	public function testRSS0(): void
	{
		$config_manager = resolve(ConfigManager::class);
		// save initial value
		$init_config_value = $config_manager->getValue('rss_enable');

		try {
			// set to 0
			Configs::set('rss_enable', '0');

			// check redirection
			$response = $this->get('/feed');
			$this->assertStatus($response, 412);
		} catch (\Exception $e) {
			// handle exception
			$this->assertTrue(false, 'Exception occurred: ' . $e->getMessage());
		} finally {
			Configs::set('rss_enable', $init_config_value);
		}
	}

	public function testRSS1(): void
	{
		// save initial value
		$config_manager = resolve(ConfigManager::class);
		$init_config_value = $config_manager->getValue('rss_enable');

		try {
			// set to 1
			Configs::set('rss_enable', '1');

			// check redirection
			$response = $this->get('/feed');
			$this->assertOk($response);
		} catch (\Exception $e) {
			// handle exception
			$this->assertTrue(false, 'Exception occurred: ' . $e->getMessage());
		} finally {
			Configs::set('rss_enable', $init_config_value);
		}
	}

	/**
	 * A single photo (one row in `photos`) that belongs to two albums (two rows
	 * in `photo_album`) must appear exactly once in the RSS feed: as a single
	 * item that links to the newest of its albums (by `created_at`) and lists
	 * every one of its albums as a `<category>`.
	 */
	public function testRSSPhotoInMultipleAlbumsAppearsOnceWithAllCategories(): void
	{
		$config_manager = resolve(ConfigManager::class);
		$init_config_value = $config_manager->getValue('rss_enable');

		try {
			Configs::set('rss_enable', '1');

			// One photo, two album memberships.
			$photo = Photo::factory()
				->owned_by($this->admin)
				->in($this->album1)
				->create();
			$photo->albums()->attach($this->album2->id);

			// The item links to whichever album is newest by created_at; compute
			// it here so the test is robust to the fixtures' creation order.
			$newest = $this->album1->created_at >= $this->album2->created_at ? $this->album1 : $this->album2;
			$older = $newest->id === $this->album1->id ? $this->album2 : $this->album1;

			$response = $this->actingAs($this->admin)->get('/feed');
			$this->assertOk($response);
			$content = $response->getContent();

			// The `<link>` carries the album the item points at (the `<guid>` is
			// now a photo-scoped identifier), so it marks both the single item
			// and which album that item links to.
			$link_newest = '<link>' . route('gallery', ['albumId' => $newest->id, 'photoId' => $photo->id]) . '</link>';
			$link_older = '<link>' . route('gallery', ['albumId' => $older->id, 'photoId' => $photo->id]) . '</link>';

			// Exactly one item, linking to the newest album only.
			$this->assertSame(1, substr_count($content, $link_newest), 'photo should appear once, linked to the newest album');
			$this->assertSame(0, substr_count($content, $link_older), 'photo must not produce a second item for the older album');

			// Both albums appear as categories on that one item (Blade escapes the text).
			$this->assertStringContainsString('<category>' . e($this->album1->title) . '</category>', $content, 'album1 should be a category');
			$this->assertStringContainsString('<category>' . e($this->album2->title) . '</category>', $content, 'album2 should be a category');
		} catch (\Exception $e) {
			$this->assertTrue(false, 'Exception occurred: ' . $e->getMessage());
		} finally {
			Configs::set('rss_enable', $init_config_value);
		}
	}

	/**
	 * A photo's `<guid>` identifies the photo itself, so it must stay constant
	 * even as the photo's album membership (and therefore its `<link>`) changes.
	 */
	public function testRSSPhotoGuidIsStableAcrossAlbumMembership(): void
	{
		$config_manager = resolve(ConfigManager::class);
		$init_config_value = $config_manager->getValue('rss_enable');

		try {
			Configs::set('rss_enable', '1');

			$photo = Photo::factory()->owned_by($this->admin)->in($this->album1)->create();

			// GUID while the photo is in a single album. It identifies the photo,
			// not the album, so it must not embed the album id.
			$guid = $this->fetchPhotoGuid($photo->id);
			$this->assertStringNotContainsString($this->album1->id, $guid, 'GUID must not embed the album id');

			// Adding a second album must not change it.
			$photo->albums()->attach($this->album2->id);
			$this->assertSame($guid, $this->fetchPhotoGuid($photo->id), 'GUID must be stable when an album is added');

			// Nor must moving the photo to a different album entirely.
			$photo->albums()->detach($this->album1->id);
			$this->assertSame($guid, $this->fetchPhotoGuid($photo->id), 'GUID must be stable when album membership changes');
		} catch (\Exception $e) {
			$this->assertTrue(false, 'Exception occurred: ' . $e->getMessage());
		} finally {
			Configs::set('rss_enable', $init_config_value);
		}
	}

	/**
	 * Fetches the RSS feed as admin and returns the `<guid>` text of the single
	 * feed item belonging to the given photo.
	 */
	private function fetchPhotoGuid(string $photo_id): string
	{
		$content = $this->actingAs($this->admin)->get('/feed')->getContent();
		self::assertIsString($content);

		$count = preg_match_all('#<guid[^>]*>([^<]*' . preg_quote($photo_id, '#') . '[^<]*)</guid>#', $content, $matches);
		self::assertSame(1, $count, 'feed should contain exactly one guid for the photo');

		return $matches[1][0];
	}
}