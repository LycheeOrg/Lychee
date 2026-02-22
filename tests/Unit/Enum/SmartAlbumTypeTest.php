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

namespace Tests\Unit\Enum;

use App\Enum\SmartAlbumType;
use App\Repositories\ConfigManager;
use LycheeVerify\Contract\VerifyInterface;
use Mockery\MockInterface;
use Tests\AbstractTestCase;
use Tests\Traits\RequireSupport;

class SmartAlbumTypeTest extends AbstractTestCase
{
	use RequireSupport;

	private ConfigManager|MockInterface $configManager;

	protected function setUp(): void
	{
		parent::setUp();
		$this->configManager = \Mockery::mock(ConfigManager::class);
	}

	protected function tearDown(): void
	{
		\Mockery::close();
		parent::tearDown();
	}

	/**
	 * Test that all expected enum values exist.
	 */
	public function testEnumValues(): void
	{
		// Original smart albums
		self::assertEquals('unsorted', SmartAlbumType::UNSORTED->value);
		self::assertEquals('highlighted', SmartAlbumType::HIGHLIGHTED->value);
		self::assertEquals('recent', SmartAlbumType::RECENT->value);
		self::assertEquals('on_this_day', SmartAlbumType::ON_THIS_DAY->value);
		self::assertEquals('untagged', SmartAlbumType::UNTAGGED->value);

		// Rating-based smart albums (Feature 009)
		self::assertEquals('unrated', SmartAlbumType::UNRATED->value);
		self::assertEquals('one_star', SmartAlbumType::ONE_STAR->value);
		self::assertEquals('two_stars', SmartAlbumType::TWO_STARS->value);
		self::assertEquals('three_stars', SmartAlbumType::THREE_STARS->value);
		self::assertEquals('four_stars', SmartAlbumType::FOUR_STARS->value);
		self::assertEquals('five_stars', SmartAlbumType::FIVE_STARS->value);
		self::assertEquals('best_pictures', SmartAlbumType::BEST_PICTURES->value);
	}

	/**
	 * Test that all 12 smart album types exist.
	 */
	public function testEnumCount(): void
	{
		$cases = SmartAlbumType::cases();
		self::assertCount(14, $cases);
	}

	/**
	 * Test is_enabled for UNRATED smart album.
	 */
	public function testUnratedIsEnabled(): void
	{
		$this->configManager->shouldReceive('getValueAsBool')
			->once()
			->with('enable_unrated')
			->andReturn(true);

		self::assertTrue(SmartAlbumType::UNRATED->is_enabled($this->configManager));
	}

	/**
	 * Test is_enabled for UNRATED smart album when disabled.
	 */
	public function testUnratedIsDisabled(): void
	{
		$this->configManager->shouldReceive('getValueAsBool')
			->once()
			->with('enable_unrated')
			->andReturn(false);

		self::assertFalse(SmartAlbumType::UNRATED->is_enabled($this->configManager));
	}

	/**
	 * Test is_enabled for ONE_STAR smart album.
	 */
	public function testOneStarIsEnabled(): void
	{
		$this->configManager->shouldReceive('getValueAsBool')
			->once()
			->with('enable_1_star')
			->andReturn(true);

		self::assertTrue(SmartAlbumType::ONE_STAR->is_enabled($this->configManager));
	}

	/**
	 * Test is_enabled for TWO_STARS smart album.
	 */
	public function testTwoStarsIsEnabled(): void
	{
		$this->configManager->shouldReceive('getValueAsBool')
			->once()
			->with('enable_2_stars')
			->andReturn(true);

		self::assertTrue(SmartAlbumType::TWO_STARS->is_enabled($this->configManager));
	}

	/**
	 * Test is_enabled for THREE_STARS smart album.
	 */
	public function testThreeStarsIsEnabled(): void
	{
		$this->configManager->shouldReceive('getValueAsBool')
			->once()
			->with('enable_3_stars')
			->andReturn(true);

		self::assertTrue(SmartAlbumType::THREE_STARS->is_enabled($this->configManager));
	}

	/**
	 * Test is_enabled for FOUR_STARS smart album.
	 */
	public function testFourStarsIsEnabled(): void
	{
		$this->configManager->shouldReceive('getValueAsBool')
			->once()
			->with('enable_4_stars')
			->andReturn(true);

		self::assertTrue(SmartAlbumType::FOUR_STARS->is_enabled($this->configManager));
	}

	/**
	 * Test is_enabled for FIVE_STARS smart album.
	 */
	public function testFiveStarsIsEnabled(): void
	{
		$this->configManager->shouldReceive('getValueAsBool')
			->once()
			->with('enable_5_stars')
			->andReturn(true);

		self::assertTrue(SmartAlbumType::FIVE_STARS->is_enabled($this->configManager));
	}

	/**
	 * Test is_enabled for BEST_PICTURES requires both config AND Lychee SE.
	 */
	public function testBestPicturesRequiresBothConfigAndSE(): void
	{
		// Register supporter verifier
		$this->app->instance(VerifyInterface::class, $this->getSupporter());

		$this->configManager->shouldReceive('getValueAsBool')
			->once()
			->with('enable_best_pictures')
			->andReturn(true);

		self::assertTrue(SmartAlbumType::BEST_PICTURES->is_enabled($this->configManager));
	}

	/**
	 * Test is_enabled for BEST_PICTURES is disabled when config is false.
	 */
	public function testBestPicturesDisabledWhenConfigFalse(): void
	{
		// Even with SE, config disabled means album is disabled
		$this->app->instance(VerifyInterface::class, $this->getSupporter());

		$this->configManager->shouldReceive('getValueAsBool')
			->once()
			->with('enable_best_pictures')
			->andReturn(false);

		self::assertFalse(SmartAlbumType::BEST_PICTURES->is_enabled($this->configManager));
	}

	/**
	 * Test is_enabled for BEST_PICTURES is disabled when Lychee SE not active.
	 */
	public function testBestPicturesDisabledWithoutSE(): void
	{
		// Register free verifier (no SE)
		$this->app->instance(VerifyInterface::class, $this->getFree());

		$this->configManager->shouldReceive('getValueAsBool')
			->once()
			->with('enable_best_pictures')
			->andReturn(true);

		self::assertFalse(SmartAlbumType::BEST_PICTURES->is_enabled($this->configManager));
	}

	/**
	 * Test original smart albums still work correctly.
	 */
	public function testOriginalSmartAlbumsIsEnabled(): void
	{
		$this->configManager->shouldReceive('getValueAsBool')
			->once()
			->with('enable_unsorted')
			->andReturn(true);

		self::assertTrue(SmartAlbumType::UNSORTED->is_enabled($this->configManager));
	}

	/**
	 * Test tryFrom with valid value.
	 */
	public function testTryFromValidValue(): void
	{
		$type = SmartAlbumType::tryFrom('unrated');
		self::assertNotNull($type);
		self::assertEquals(SmartAlbumType::UNRATED, $type);
	}

	/**
	 * Test tryFrom with invalid value.
	 */
	public function testTryFromInvalidValue(): void
	{
		$type = SmartAlbumType::tryFrom('invalid_album');
		self::assertNull($type);
	}
}
