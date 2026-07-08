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

use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use App\Repositories\ConfigManager;
use Exception;
use Illuminate\Support\Facades\DB;
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
	 * A photo shared into an album the viewer cannot access must not leak that
	 * album: neither as the item's `<link>` nor as a `<category>`. The album's
	 * owner, however, must still see it.
	 */
	public function testRSSInaccessibleAlbumIsNotExposed(): void
	{
		$config_manager = resolve(ConfigManager::class);
		$init_config_value = $config_manager->getValue('rss_enable');

		try {
			Configs::set('rss_enable', '1');

			// album4 is public (a guest may reach it); the private album is only
			// owned by userMayUpload1 and has no public permission. It is created
			// last, so it is the newest album — pre-fix it would have been chosen
			// as the item's link.
			$public_album = $this->album4;
			$private_album = Album::factory()->as_root()->owned_by($this->userMayUpload1)->create();

			$photo = Photo::factory()->owned_by($this->userMayUpload1)->in($public_album)->create();
			$photo->albums()->attach($private_album->id);

			$public_link = '<link>' . route('gallery', ['albumId' => $public_album->id, 'photoId' => $photo->id]) . '</link>';
			$private_link = '<link>' . route('gallery', ['albumId' => $private_album->id, 'photoId' => $photo->id]) . '</link>';
			$public_category = '<category>' . e($public_album->title) . '</category>';
			$private_category = '<category>' . e($private_album->title) . '</category>';

			// A guest reaches the photo only via the public album; the private
			// album must be completely absent.
			$guest_content = $this->get('/feed')->getContent();
			self::assertIsString($guest_content);
			$this->assertSame(1, substr_count($guest_content, $public_link), 'guest item links to the public album');
			$this->assertSame(0, substr_count($guest_content, $private_link), 'guest item must not link to the inaccessible album');
			$this->assertStringContainsString($public_category, $guest_content, 'public album is a category for the guest');
			$this->assertStringNotContainsString($private_category, $guest_content, 'inaccessible album must not be a category for the guest');

			// The owner of the private album sees both albums as categories.
			$owner_content = $this->actingAs($this->userMayUpload1)->get('/feed')->getContent();
			self::assertIsString($owner_content);
			$this->assertStringContainsString($public_category, $owner_content, 'owner still sees the public album');
			$this->assertStringContainsString($private_category, $owner_content, 'owner sees their own private album');
		} catch (\Exception $e) {
			$this->assertTrue(false, 'Exception occurred: ' . $e->getMessage());
		} finally {
			Configs::set('rss_enable', $init_config_value);
		}
	}

	/**
	 * A photo shared into a sensitive (NSFW) album must not expose that album as
	 * a `<link>` or `<category>` when `hide_nsfw_in_rss` is enabled, while a
	 * non-sensitive album it also belongs to still surfaces. Disabling the
	 * setting exposes the sensitive album again.
	 */
	public function testRSSNsfwAlbumHiddenWhenConfigured(): void
	{
		$config_manager = resolve(ConfigManager::class);
		$init_rss = $config_manager->getValue('rss_enable');
		$init_nsfw = $config_manager->getValue('hide_nsfw_in_rss');

		try {
			Configs::set('rss_enable', '1');

			// A normal album keeps the photo visible in the feed; the sensitive
			// album is the one whose exposure we are testing.
			$normal_album = Album::factory()->as_root()->owned_by($this->admin)->create();
			$nsfw_album = Album::factory()->as_root()->owned_by($this->admin)->create();
			DB::table('base_albums')->where('id', $nsfw_album->id)->update(['is_nsfw' => true]);

			$photo = Photo::factory()->owned_by($this->admin)->in($normal_album)->create();
			$photo->albums()->attach($nsfw_album->id);

			$normal_category = '<category>' . e($normal_album->title) . '</category>';
			$nsfw_category = '<category>' . e($nsfw_album->title) . '</category>';
			$nsfw_link = '<link>' . route('gallery', ['albumId' => $nsfw_album->id, 'photoId' => $photo->id]) . '</link>';

			// With hiding enabled (the safe default), the sensitive album is gone
			// but the photo is still present via the normal album.
			Configs::set('hide_nsfw_in_rss', '1');
			$hidden_content = $this->actingAs($this->admin)->get('/feed')->getContent();
			self::assertIsString($hidden_content);
			$this->assertStringContainsString($normal_category, $hidden_content, 'non-sensitive album is a category');
			$this->assertStringNotContainsString($nsfw_category, $hidden_content, 'sensitive album must not be a category');
			$this->assertSame(0, substr_count($hidden_content, $nsfw_link), 'item must not link to the sensitive album');

			// With hiding disabled, the sensitive album is exposed again.
			Configs::set('hide_nsfw_in_rss', '0');
			$shown_content = $this->actingAs($this->admin)->get('/feed')->getContent();
			self::assertIsString($shown_content);
			$this->assertStringContainsString($nsfw_category, $shown_content, 'sensitive album is a category when hiding is disabled');
		} catch (\Exception $e) {
			$this->assertTrue(false, 'Exception occurred: ' . $e->getMessage());
		} finally {
			Configs::set('rss_enable', $init_rss);
			Configs::set('hide_nsfw_in_rss', $init_nsfw);
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