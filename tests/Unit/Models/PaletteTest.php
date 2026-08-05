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

namespace Tests\Unit\Models;

use App\Models\Palette;
use Tests\AbstractTestCase;

class PaletteTest extends AbstractTestCase
{
	public function testToHexConvertsIntegerToHexString(): void
	{
		self::assertEquals('#ffcc00', Palette::toHex(0xFFCC00));
		self::assertEquals('#000000', Palette::toHex(0x000000));
		self::assertEquals('#ffffff', Palette::toHex(0xFFFFFF));
		self::assertEquals('#010203', Palette::toHex(0x010203));
	}
}
