<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
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

namespace Tests\Unit\Models;

use App\Models\Photo;
use Tests\AbstractTestCase;

class PhotoTest extends AbstractTestCase
{
	public function testShutter(): void
	{
		$p = new Photo();
		$p->shutter = '10/5 s';
		self::assertEquals('2 s', $p->shutter);
		$p->shutter = '1/1000 s';
		self::assertEquals('1/1000 s', $p->shutter);
		$p->shutter = '1/1 s';
		self::assertEquals('1 s', $p->shutter);
	}

	public function testShutterException(): void
	{
		$p = new Photo();
		$p->shutter = '10/0 s';
		self::assertEquals('10/0 s', $p->shutter);
	}

	public function testAspectRatio(): void
	{
		$p = new Photo();
		$p->type = 'video/mp4';
		self::assertEquals('1', $p->aspect_ratio);

		$p->type = 'image/jpeg';
		self::assertEquals('1', $p->aspect_ratio);
	}
}
