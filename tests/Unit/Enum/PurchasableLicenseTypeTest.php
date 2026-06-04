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

use App\Enum\PurchasableLicenseType;
use Tests\AbstractTestCase;

/**
 * Unit tests for PurchasableLicenseType enum.
 *
 * Tests T-043-38: verifies that PRINT case exists and serialises / deserialises correctly.
 */
class PurchasableLicenseTypeTest extends AbstractTestCase
{
	public function testPrintCaseExists(): void
	{
		$case = PurchasableLicenseType::PRINT;
		$this->assertInstanceOf(PurchasableLicenseType::class, $case);
	}

	public function testPrintCaseValue(): void
	{
		$this->assertSame('print', PurchasableLicenseType::PRINT->value);
	}

	public function testPrintCaseCanBeCreatedFromValue(): void
	{
		$case = PurchasableLicenseType::from('print');
		$this->assertSame(PurchasableLicenseType::PRINT, $case);
	}

	public function testPrintCaseTryFrom(): void
	{
		$case = PurchasableLicenseType::tryFrom('print');
		$this->assertNotNull($case);
		$this->assertSame(PurchasableLicenseType::PRINT, $case);
	}

	public function testUnknownValueReturnsNullOnTryFrom(): void
	{
		$case = PurchasableLicenseType::tryFrom('not_a_license');
		$this->assertNull($case);
	}

	public function testAllExpectedCasesPresent(): void
	{
		$cases = array_map(fn (PurchasableLicenseType $c) => $c->value, PurchasableLicenseType::cases());
		$this->assertContains('personal', $cases);
		$this->assertContains('commercial', $cases);
		$this->assertContains('extended', $cases);
		$this->assertContains('print', $cases);
	}
}
