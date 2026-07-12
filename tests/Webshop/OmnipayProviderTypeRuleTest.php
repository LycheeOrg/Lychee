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

namespace Tests\Webshop;

use App\Enum\OmnipayProviderType;
use App\Rules\OmnipayProviderTypeRule;
use Tests\AbstractTestCase;

class OmnipayProviderTypeRuleTest extends AbstractTestCase
{
	public function testValidProviderPasses(): void
	{
		$rule = new OmnipayProviderTypeRule(allow_nullable: false);
		$msg = "don't worry";
		$rule->validate('provider', OmnipayProviderType::DUMMY->value, function ($message) use (&$msg): void { $msg = $message; });
		self::assertEquals("don't worry", $msg);
	}

	public function testAllValidProvidersPass(): void
	{
		$rule = new OmnipayProviderTypeRule(allow_nullable: false);
		foreach (OmnipayProviderType::cases() as $provider) {
			if (!$provider->isAllowed()) {
				continue;
			}

			$msg = "don't worry";
			$rule->validate('provider', $provider->value, function ($message) use (&$msg): void { $msg = $message; });
			self::assertEquals("don't worry", $msg, "Provider {$provider->value} was expected to be valid.");
		}
	}

	public function testUnknownProviderFails(): void
	{
		$rule = new OmnipayProviderTypeRule(allow_nullable: false);
		$msg = "don't worry";
		$rule->validate('provider', 'NotAProvider', function ($message) use (&$msg): void { $msg = $message; });
		self::assertEquals('Error: This provider NotAProvider is not allowed.', $msg);
	}

	public function testEmptyStringFails(): void
	{
		$rule = new OmnipayProviderTypeRule(allow_nullable: false);
		$msg = "don't worry";
		$rule->validate('provider', '', function ($message) use (&$msg): void { $msg = $message; });
		self::assertEquals('Error: This provider  is not allowed.', $msg);
	}

	public function testNonStringValueFails(): void
	{
		$rule = new OmnipayProviderTypeRule(allow_nullable: false);
		$msg = "don't worry";
		$rule->validate('provider', 1234, function ($message) use (&$msg): void { $msg = $message; });
		self::assertEquals('Error: This provider must be a string.', $msg);
	}

	public function testArrayValueFails(): void
	{
		$rule = new OmnipayProviderTypeRule(allow_nullable: false);
		$msg = "don't worry";
		$rule->validate('provider', ['not', 'a', 'string'], function ($message) use (&$msg): void { $msg = $message; });
		self::assertEquals('Error: This provider must be a string.', $msg);
	}

	public function testNullFailsWhenNotNullable(): void
	{
		$rule = new OmnipayProviderTypeRule(allow_nullable: false);
		$msg = "don't worry";
		$rule->validate('provider', null, function ($message) use (&$msg): void { $msg = $message; });
		self::assertEquals('Error: This provider null is not allowed.', $msg);
	}

	public function testNullPassesWhenNullable(): void
	{
		$rule = new OmnipayProviderTypeRule(allow_nullable: true);
		$msg = "don't worry";
		$rule->validate('provider', null, function ($message) use (&$msg): void { $msg = $message; });
		self::assertEquals("don't worry", $msg);
	}

	public function testValidProviderStillPassesWhenNullable(): void
	{
		$rule = new OmnipayProviderTypeRule(allow_nullable: true);
		$msg = "don't worry";
		$rule->validate('provider', OmnipayProviderType::STRIPE->value, function ($message) use (&$msg): void { $msg = $message; });
		self::assertEquals("don't worry", $msg);
	}

	public function testInvalidProviderStillFailsWhenNullable(): void
	{
		$rule = new OmnipayProviderTypeRule(allow_nullable: true);
		$msg = "don't worry";
		$rule->validate('provider', 'NotAProvider', function ($message) use (&$msg): void { $msg = $message; });
		self::assertEquals('Error: This provider NotAProvider is not allowed.', $msg);
	}

	public function testDummyProviderNotAllowedInProduction(): void
	{
		config(['app.env' => 'production']);

		$rule = new OmnipayProviderTypeRule(allow_nullable: false);
		$msg = "don't worry";
		$rule->validate('provider', OmnipayProviderType::DUMMY->value, function ($message) use (&$msg): void { $msg = $message; });
		self::assertEquals('Error: This provider Dummy is not allowed.', $msg);
	}
}
