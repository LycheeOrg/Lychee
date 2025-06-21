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

use App\Models\Colour;
use Tests\AbstractTestCase;
use Tests\Traits\RequiresEmptyColourPalettes;

class ColourTest extends AbstractTestCase
{
	use RequiresEmptyColourPalettes;

	protected function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresEmptyColourPalettes();
	}

	protected function tearDown(): void
	{
		$this->tearDownRequiresEmptyColourPalettes();
		parent::tearDown();
	}

	public function testWrongColour(): void
	{
		$this->expectException(\InvalidArgumentException::class);

		Colour::fromHex('#00000000');
	}

	public function testColourEquality(): void
	{
		$c = Colour::fromHex('#FFCC00');
		$this->assertEquals(0xFFCC00, $c->id);
		$this->assertEquals('#ffcc00', $c->toHex());
		$this->assertEquals(255, $c->R);
		$this->assertEquals(204, $c->G);
		$this->assertEquals(0, $c->B);
	}
}