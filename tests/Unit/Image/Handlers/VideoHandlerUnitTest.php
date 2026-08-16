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

namespace Tests\Unit\Image\Handlers;

use App\Image\Handlers\VideoHandler;
use App\Models\Configs;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\AbstractTestCase;

class VideoHandlerUnitTest extends AbstractTestCase
{
	use DatabaseTransactions;

	public function testMiddleModeUsesHalfOfDuration(): void
	{
		Configs::set('video_thumbnail_frame_mode', 'middle');

		self::assertEquals(5.0, VideoHandler::resolveThumbnailFramePosition('10'));
		self::assertEquals(2.5, VideoHandler::resolveThumbnailFramePosition('5.0'));
	}

	public function testMiddleModeFallsBackToZeroWithoutDuration(): void
	{
		Configs::set('video_thumbnail_frame_mode', 'middle');

		self::assertEquals(0.0, VideoHandler::resolveThumbnailFramePosition(null));
		self::assertEquals(0.0, VideoHandler::resolveThumbnailFramePosition('not-a-number'));
	}

	public function testFirstModeAlwaysReturnsZero(): void
	{
		Configs::set('video_thumbnail_frame_mode', 'first');

		self::assertEquals(0.0, VideoHandler::resolveThumbnailFramePosition('10'));
		self::assertEquals(0.0, VideoHandler::resolveThumbnailFramePosition(null));
	}

	public function testCustomModeUsesConfiguredSeconds(): void
	{
		Configs::set('video_thumbnail_frame_mode', 'custom');
		Configs::set('video_thumbnail_frame_seconds', 7);

		self::assertEquals(7.0, VideoHandler::resolveThumbnailFramePosition('100'));
		self::assertEquals(7.0, VideoHandler::resolveThumbnailFramePosition(null));
	}
}
