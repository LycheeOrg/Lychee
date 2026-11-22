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

namespace Tests\Unit\Actions\Sitemap;

use App\Actions\Sitemap\SitemapLastmod;
use App\Actions\Sitemap\SitemapRenderer;
use function Safe\simplexml_load_string;
use Tests\AbstractTestCase;

/**
 * Feature 117 (FR-117-06, FR-117-07, S-117-02, S-117-07, S-117-18): XML of the sitemap.
 */
class SitemapRendererTest extends AbstractTestCase
{
	private const ROOT = 'https://example.com/gallery';
	/** 2026-11-22 12:00:00 UTC. */
	private const NOW = 1_795_348_800;

	private SitemapRenderer $renderer;
	private \Closure $album_url;

	public function setUp(): void
	{
		parent::setUp();
		$this->renderer = new SitemapRenderer(new SitemapLastmod());
		$this->album_url = fn (string $id): string => self::ROOT . '/' . $id;
	}

	public function testEmptyGalleryListsTheRootOnly(): void
	{
		$xml = $this->renderer->render(self::ROOT, $this->album_url, [], self::NOW);

		self::assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $xml);
		$doc = $this->parse($xml);
		self::assertSame(SitemapRenderer::XMLNS, $doc->getNamespaces()['']);
		self::assertCount(1, $doc->url);
		self::assertSame(self::ROOT, (string) $doc->url[0]->loc);
		self::assertCount(0, $doc->url[0]->lastmod);
	}

	public function testRootFirstThenAlbumsInGivenOrder(): void
	{
		$xml = $this->renderer->render(self::ROOT, $this->album_url, [
			$this->row('aaaaaaaaaaaaaaaaaaaaaaaa', null, '2026-01-02 03:04:05', null),
			$this->row('bbbbbbbbbbbbbbbbbbbbbbbb', null, '2026-01-02 03:04:05', '2026-03-04 05:06:07'),
		], self::NOW);

		$doc = $this->parse($xml);
		self::assertCount(3, $doc->url);
		self::assertSame(self::ROOT, (string) $doc->url[0]->loc);
		self::assertSame(self::ROOT . '/aaaaaaaaaaaaaaaaaaaaaaaa', (string) $doc->url[1]->loc);
		self::assertSame('2026-01-02T03:04:05+00:00', (string) $doc->url[1]->lastmod);
		self::assertSame(self::ROOT . '/bbbbbbbbbbbbbbbbbbbbbbbb', (string) $doc->url[2]->loc);
		self::assertSame('2026-03-04T05:06:07+00:00', (string) $doc->url[2]->lastmod);
	}

	public function testSlugIsPreferredOverId(): void
	{
		$xml = $this->renderer->render(self::ROOT, $this->album_url, [
			$this->row('aaaaaaaaaaaaaaaaaaaaaaaa', 'summer-2026', '2026-01-02 03:04:05', null),
			$this->row('bbbbbbbbbbbbbbbbbbbbbbbb', '', '2026-01-02 03:04:05', null),
		], self::NOW);

		$doc = $this->parse($xml);
		self::assertSame(self::ROOT . '/summer-2026', (string) $doc->url[1]->loc);
		self::assertSame(self::ROOT . '/bbbbbbbbbbbbbbbbbbbbbbbb', (string) $doc->url[2]->loc);
	}

	public function testLastmodIsOmittedWithoutDates(): void
	{
		$xml = $this->renderer->render(self::ROOT, $this->album_url, [
			$this->row('aaaaaaaaaaaaaaaaaaaaaaaa', null, null, null),
		], self::NOW);

		$doc = $this->parse($xml);
		self::assertCount(0, $doc->url[1]->lastmod);
	}

	public function testLocIsEscaped(): void
	{
		$url = fn (string $id): string => self::ROOT . '/' . $id . '?a=1&b=<"\'>';
		$xml = $this->renderer->render(self::ROOT . '?x=1&y=2', $url, [
			$this->row('aaaaaaaaaaaaaaaaaaaaaaaa', null, null, null),
		], self::NOW);

		self::assertStringContainsString('<loc>' . self::ROOT . '?x=1&amp;y=2</loc>', $xml);
		self::assertStringContainsString('?a=1&amp;b=&lt;&quot;&apos;&gt;</loc>', $xml);
		$doc = $this->parse($xml);
		self::assertSame(self::ROOT . '/aaaaaaaaaaaaaaaaaaaaaaaa?a=1&b=<"\'>', (string) $doc->url[1]->loc);
	}

	/**
	 * @return array{id:string,slug:string|null,updated_at:string|null,max_taken_at:string|null}
	 */
	private function row(string $id, ?string $slug, ?string $updated_at, ?string $max_taken_at): array
	{
		return ['id' => $id, 'slug' => $slug, 'updated_at' => $updated_at, 'max_taken_at' => $max_taken_at];
	}

	/**
	 * Throws when the sitemap is not well-formed XML.
	 */
	private function parse(string $xml): \SimpleXMLElement
	{
		return simplexml_load_string($xml);
	}
}
