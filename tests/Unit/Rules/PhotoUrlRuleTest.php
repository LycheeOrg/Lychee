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

namespace Tests\Unit\Rules;

use App\DTO\UrlValidatedDTO;
use App\Exceptions\Internal\LycheeLogicException;
use App\Rules\PhotoUrlRule;
use Tests\AbstractTestCase;

class PhotoUrlRuleTest extends AbstractTestCase
{
	private PhotoUrlRule $rule;
	private bool $fail_called = false;
	private string $fail_message = '';

	public function setUp(): void
	{
		parent::setUp();
		$this->rule = new PhotoUrlRule();
		$this->fail_called = false;
		$this->fail_message = '';
	}

	public function m(string $message)
	{
		$this->fail_called = true;
		$this->fail_message = $message;
	}

	/**
	 * Test validation with a non-DTO value.
	 */
	public function testNonDtoValueThrows(): void
	{
		$this->expectException(LycheeLogicException::class);
		$this->expectExceptionMessage('The value passed to the PhotoUrlRule must be an instance of UrlValidatedDTO. Got int');

		$this->rule->validate('photo_url', 123, fn ($m) => $this->m($m));
	}

	/**
	 * Test validation with a DTO containing an error.
	 */
	public function testValueWithErrorFailsValidation(): void
	{
		$value = UrlValidatedDTO::fromError('https://example.com/file.jpg', 'must be a valid HTTPS URL.');

		$this->rule->validate('photo_url', $value, fn ($m) => $this->m($m));

		self::assertTrue($this->fail_called);
		self::assertEquals('photo_url must be a valid HTTPS URL.', $this->fail_message);
	}

	/**
	 * Test validation with a DTO missing resolved IP.
	 */
	public function testValueWithoutResolvedIpFailsValidation(): void
	{
		$value = new UrlValidatedDTO(
			url: 'https://example.com/file.jpg',
			resolved_ip: null,
			error: null,
		);

		$this->rule->validate('photo_url', $value, fn ($m) => $this->m($m));

		self::assertTrue($this->fail_called);
		self::assertEquals('photo_url did not resolve to a valid IP address.', $this->fail_message);
	}

	/**
	 * Test validation succeeds with DTO that has no error and a resolved IP.
	 */
	public function testValidValuePassesValidation(): void
	{
		$value = new UrlValidatedDTO(
			url: 'https://example.com/file.jpg',
			resolved_ip: '93.184.216.34',
			error: null,
		);

		$this->rule->validate('photo_url', $value, fn ($m) => $this->m($m));

		self::assertFalse($this->fail_called);
		self::assertEquals('', $this->fail_message);
	}
}
