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

namespace Tests\Feature_v2\Sitemap;

use App\Models\Configs;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use function Safe\simplexml_load_string;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * Feature 117: `GET /sitemap.xml`.
 */
class SitemapTest extends BaseApiWithDataTest
{
	/**
	 * CFG-117-01 … 03: defaults, categories and dependencies of the settings.
	 */
	public function testConfigRows(): void
	{
		$rows = DB::table('configs')
			->whereIn('key', ['sitemap_enable', 'sitemap_root_only', 'hide_nsfw_in_sitemap'])
			->get(['key', 'value', 'cat', 'type_range', 'required_keys', 'level'])
			->keyBy('key');

		self::assertCount(3, $rows);

		self::assertSame('0', $rows['sitemap_enable']->value);
		self::assertSame('config', $rows['sitemap_enable']->cat);
		self::assertNull($rows['sitemap_enable']->required_keys);

		self::assertSame('0', $rows['sitemap_root_only']->value);
		self::assertSame('config', $rows['sitemap_root_only']->cat);
		self::assertSame('sitemap_enable', $rows['sitemap_root_only']->required_keys);

		self::assertSame('1', $rows['hide_nsfw_in_sitemap']->value);
		self::assertSame('Mod NSFW', $rows['hide_nsfw_in_sitemap']->cat);
		self::assertSame('sitemap_enable', $rows['hide_nsfw_in_sitemap']->required_keys);

		foreach ($rows as $row) {
			self::assertSame('0|1', $row->type_range);
			self::assertSame(0, (int) $row->level);
		}
	}

	/**
	 * S-117-01: off by default, the route answers 404.
	 */
	public function testDisabledAnswers404(): void
	{
		$this->assertNotFound($this->get('/sitemap.xml'));
	}

	/**
	 * S-117-02, S-117-03, S-117-10, S-117-17: root first, public albums, no
	 * private album, no photo; XML and cache headers, no cookie.
	 */
	public function testEnabled(): void
	{
		$this->withConfigs(['sitemap_enable' => '1'], function (): void {
			$response = $this->get('/sitemap.xml');

			$this->assertOk($response);
			$response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
			$cache_control = strval($response->headers->get('Cache-Control'));
			self::assertStringContainsString('public', $cache_control);
			self::assertStringContainsString('max-age=3600', $cache_control);
			self::assertStringNotContainsString('private', $cache_control);
			self::assertFalse($response->headers->has('Set-Cookie'), 'the sitemap must not start a session');

			$locs = $this->locs($response);
			self::assertSame([
				route('gallery'),
				route('gallery', ['albumId' => $this->album4->id]),
				route('gallery', ['albumId' => $this->subAlbum4->id]),
			], $locs);

			$body = strval($response->getContent());
			self::assertStringNotContainsString($this->album1->id, $body);
			self::assertStringNotContainsString($this->photo4->id, $body);
			self::assertStringNotContainsString($this->subPhoto4->id, $body);
		});
	}

	/**
	 * S-117-17: crawlers that ask for XML get it.
	 */
	public function testAcceptsXmlRequests(): void
	{
		$this->withConfigs(['sitemap_enable' => '1'], function (): void {
			$this->assertOk($this->withHeaders(['Accept' => 'application/xml'])->get('/sitemap.xml'));
		});
	}

	/**
	 * S-117-07: an album with a slug is addressed by its slug.
	 */
	public function testSlugUrl(): void
	{
		$this->album4->slug = 'public-album';
		$this->album4->save();

		$this->withConfigs(['sitemap_enable' => '1'], function (): void {
			self::assertContains(route('gallery', ['albumId' => 'public-album']), $this->locs($this->get('/sitemap.xml')));
		});
	}

	/**
	 * S-117-08: an admin gets the guest document.
	 */
	public function testAdminGetsTheGuestDocument(): void
	{
		$this->withConfigs(['sitemap_enable' => '1'], function (): void {
			$guest = strval($this->get('/sitemap.xml')->getContent());
			$admin = strval($this->actingAs($this->admin)->get('/sitemap.xml')->getContent());

			self::assertSame($guest, $admin);
		});
	}

	/**
	 * S-117-09 end to end: root only drops nested albums.
	 */
	public function testRootOnly(): void
	{
		$this->withConfigs(['sitemap_enable' => '1', 'sitemap_root_only' => '1'], function (): void {
			self::assertSame([
				route('gallery'),
				route('gallery', ['albumId' => $this->album4->id]),
			], $this->locs($this->get('/sitemap.xml')));
		});
	}

	/**
	 * S-117-11.
	 */
	public function testLoginRequiredAnswers404(): void
	{
		$this->withConfigs(['sitemap_enable' => '1', 'login_required' => '1'], function (): void {
			$this->assertNotFound($this->get('/sitemap.xml'));
		});
	}

	/**
	 * S-117-12.
	 */
	public function testLoginRequiredAtRootOnlyAnswers404(): void
	{
		$this->withConfigs(['sitemap_enable' => '1', 'login_required' => '1', 'login_required_root_only' => '1'], function (): void {
			$this->assertNotFound($this->get('/sitemap.xml'));
		});
	}

	/**
	 * S-117-13.
	 */
	public function testGalleryPasswordAnswers404(): void
	{
		$this->withConfigs(['sitemap_enable' => '1', 'gallery_password' => Hash::make('open sesame')], function (): void {
			$this->assertNotFound($this->get('/sitemap.xml'));
		});
	}

	/**
	 * @return list<string> the `<loc>` values in document order
	 */
	private function locs(TestResponse $response): array
	{
		$locs = [];
		foreach (simplexml_load_string(strval($response->getContent()))->url as $url) {
			$locs[] = strval($url->loc);
		}

		return $locs;
	}

	/**
	 * Runs $run with the given settings, then puts the previous values back.
	 *
	 * @param array<string,string> $values
	 */
	private function withConfigs(array $values, \Closure $run): void
	{
		$initial = [];
		foreach (array_keys($values) as $key) {
			$initial[$key] = strval(Configs::query()->where('key', '=', $key)->value('value'));
		}

		try {
			foreach ($values as $key => $value) {
				Configs::set($key, $value);
			}
			$run();
		} finally {
			foreach ($initial as $key => $value) {
				Configs::query()->where('key', '=', $key)->update(['value' => $value]);
			}
			resolve(ConfigManager::class)->invalidateCache();
		}
	}
}
