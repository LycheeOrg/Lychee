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

namespace Tests\Unit;

use App\Assets\Features;
use App\Exceptions\Internal\FeaturesDoesNotExistsException;
use Tests\AbstractTestCase;

class FeaturesUnitTest extends AbstractTestCase
{
	public function testAllAreActive(): void
	{
		self::assertFalse(Features::allAreActive(['use-s3']));
		self::assertTrue(Features::allAreActive(['require-content-type']));
	}

	public function testSomeAreActive(): void
	{
		self::assertFalse(Features::someAreActive(['use-s3']));
		self::assertTrue(Features::someAreActive(['require-content-type', 'use-s3']));
	}

	public function testAllAreInactive(): void
	{
		self::assertTrue(Features::allAreInactive(['use-s3']));
		self::assertFalse(Features::allAreInactive(['require-content-type']));
	}

	public function testSomeAreInactive(): void
	{
		self::assertFalse(Features::someAreInactive(['require-content-type']));
		self::assertTrue(Features::someAreInactive(['require-content-type', 'use-s3']));
	}

	public function testWhenArray(): void
	{
		self::assertTrue(Features::when(['require-content-type'], fn () => true, fn () => false));
	}

	public function testThrow(): void
	{
		$this->expectException(FeaturesDoesNotExistsException::class);
		Features::active('livewire');
	}
}