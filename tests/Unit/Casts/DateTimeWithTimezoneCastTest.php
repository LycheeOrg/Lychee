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

namespace Tests\Unit\Casts;

use App\Casts\DateTimeWithTimezoneCast;
use App\Exceptions\Internal\LycheeDomainException;
use App\Exceptions\Internal\LycheeInvalidArgumentException;
use App\Exceptions\Internal\LycheeLogicException;
use App\Exceptions\Internal\MissingModelAttributeException;
use App\Models\Photo;
use Illuminate\Database\Eloquent\Model;
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
		$cast->set($photo, 'created_at', $this, []);
	}

	public function testMustNotSetCastThrows(): void
	{
		self::expectException(LycheeLogicException::class);
		$cast = new DateTimeWithTimezoneCast();
		$model = new class() extends Model {};

		$cast->get($model, 'created_at', '2023-10-01 12:00:00', []);
	}
}
