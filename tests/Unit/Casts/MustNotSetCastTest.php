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

use App\Casts\MustNotSetCast;
use App\Exceptions\Internal\IllegalOrderOfOperationException;
use Illuminate\Database\Eloquent\Model;
use Tests\AbstractTestCase;

class MustNotSetCastTest extends AbstractTestCase
{
	public function testMustNotSetCastThrows(): void
	{
		self::expectException(IllegalOrderOfOperationException::class);
		$cast = new MustNotSetCast('created_at');

		$model = new class() extends Model {};

		$cast->Set($model, 'created_at', '2023-10-01 12:00:00', []);
	}
}