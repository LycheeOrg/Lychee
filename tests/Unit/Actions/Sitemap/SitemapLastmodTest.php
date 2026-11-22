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
use Tests\AbstractTestCase;

/**
 * Feature 117 (FR-117-12, S-117-16): `<lastmod>` of an album.
 */
class SitemapLastmodTest extends AbstractTestCase
{
	/** 2026-11-22 12:00:00 UTC. */
	private const NOW = 1_795_348_800;

	private SitemapLastmod $lastmod;

	public function setUp(): void
	{
		parent::setUp();
		$this->lastmod = new SitemapLastmod();
	}

	public function testNoDatesGiveNoLastmod(): void
	{
		self::assertNull($this->lastmod->compute(null, null, self::NOW));
	}

	public function testUpdatedAtAlone(): void
	{
		self::assertSame('2026-01-02T03:04:05+00:00', $this->lastmod->compute('2026-01-02 03:04:05', null, self::NOW));
	}

	public function testMaxTakenAtAlone(): void
	{
		self::assertSame('2025-05-06T07:08:09+00:00', $this->lastmod->compute(null, '2025-05-06 07:08:09', self::NOW));
	}

	public function testLaterUpdatedAtWins(): void
	{
		self::assertSame('2026-01-02T03:04:05+00:00', $this->lastmod->compute('2026-01-02 03:04:05', '2025-05-06 07:08:09', self::NOW));
	}

	public function testLaterMaxTakenAtWins(): void
	{
		self::assertSame('2026-03-04T05:06:07+00:00', $this->lastmod->compute('2026-01-02 03:04:05', '2026-03-04 05:06:07', self::NOW));
	}

	public function testFutureMaxTakenAtIsSkipped(): void
	{
		self::assertSame('2026-01-02T03:04:05+00:00', $this->lastmod->compute('2026-01-02 03:04:05', '2031-01-01 00:00:00', self::NOW));
		self::assertNull($this->lastmod->compute(null, '2031-01-01 00:00:00', self::NOW));
	}

	public function testFractionalSecondsAreDropped(): void
	{
		self::assertSame('2026-01-02T03:04:05+00:00', $this->lastmod->compute('2026-01-02 03:04:05.123456', null, self::NOW));
	}

	public function testStoredValuesAreReadAsUtcWhateverTheDefaultZone(): void
	{
		$zone = date_default_timezone_get();
		try {
			date_default_timezone_set('Pacific/Auckland');
			self::assertSame('2026-01-02T03:04:05+00:00', $this->lastmod->compute('2026-01-02 03:04:05', null, self::NOW));
		} finally {
			date_default_timezone_set($zone);
		}
	}
}
