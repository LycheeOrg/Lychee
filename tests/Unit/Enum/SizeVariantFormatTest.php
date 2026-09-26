<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace Tests\Unit\Enum;

use App\Enum\SizeVariantFormat;
use Tests\AbstractTestCase;

class SizeVariantFormatTest extends AbstractTestCase
{
	public function testExtension(): void
	{
		self::assertNull(SizeVariantFormat::ORIGINAL->extension());
		self::assertEquals('.jpeg', SizeVariantFormat::JPEG->extension());
		self::assertEquals('.webp', SizeVariantFormat::WEBP->extension());
	}
}
