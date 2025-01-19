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

namespace Tests\Unit\Casts;

use App\Casts\DateTimeWithTimezoneCast;
use App\Exceptions\Internal\LycheeDomainException;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Exceptions\Internal\MissingModelAttributeException;
use App\Models\Photo;
use Tests\AbstractTestCase;

class DateTimeWithTimezoneCastTest extends AbstractTestCase
{
	public function testGetDateTimeCastThrows(): void
	{
		self::expectException(LycheeInvalidArgumentException::class);
		$cast = new DateTimeWithTimezoneCast();
		$photo = new Photo();
		$cast->get($photo, 'created_at', 5, []);
	}

	public function testGetDateTimeCastThrowsAgain(): void
	{
		self::expectException(MissingModelAttributeException::class);
		$cast = new DateTimeWithTimezoneCast();
		$photo = new Photo();
		$cast->get($photo, 'created_at', 'is_string', []);
	}

	public function testGetDateTimeCastThrowsAgainTwice(): void
	{
		self::expectException(LycheeDomainException::class);
		$cast = new DateTimeWithTimezoneCast();
		$photo = new Photo();
		$cast->get($photo, 'created_at', 'is_string', ['created_at_orig_tz' => '']);
	}

	public function testSetDateTimeCastThrows(): void
	{
		self::expectException(LycheeInvalidArgumentException::class);
		$cast = new DateTimeWithTimezoneCast();
		$photo = new Photo();
		// @phpstan-ignore-next-line this is voluntary to trigger the exception
		$cast->set($photo, 'created_at', $this, []);
	}
}
