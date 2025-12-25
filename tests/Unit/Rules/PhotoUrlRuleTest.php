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

namespace Tests\Unit\Rules;

use App\Models\Configs;
use App\Rules\PhotoUrlRule;
use Tests\AbstractTestCase;

class PhotoUrlRuleTest extends AbstractTestCase
{
	private PhotoUrlRule $rule;
	private $failCalled = false;
	private $failMessage = '';

	public function setUp(): void
	{
		parent::setUp();
		$this->rule = resolve(PhotoUrlRule::class);
		$this->failCalled = false;
		$this->failMessage = '';

		Configs::set('import_via_url_require_https', '1');
		Configs::set('import_via_url_forbidden_ports', '1');
		Configs::set('import_via_url_forbidden_local_ip', '1');
		Configs::set('import_via_url_forbidden_localhost', '1');
	}

	public function tearDown(): void
	{
		Configs::set('import_via_url_require_https', '1');
		Configs::set('import_via_url_forbidden_ports', '1');
		Configs::set('import_via_url_forbidden_local_ip', '1');
		Configs::set('import_via_url_forbidden_localhost', '1');

		parent::tearDown();
	}

	public function m(string $message)
	{
		$this->failCalled = true;
		$this->failMessage = $message;
	}

	/**
	 * Test validation with a non-string value.
	 */
	public function testNonStringValue(): void
	{
		$this->rule->validate('photo_url', 123, fn ($m) => $this->m($m));
		self::assertTrue($this->failCalled);
		self::assertEquals('photo_url is not a string', $this->failMessage);
	}

	/**
	 * Test validation with an invalid URL format.
	 */
	public function testInvalidUrlFormat(): void
	{
		$this->rule->validate('photo_url', 'not-a-url', fn ($m) => $this->m($m));
		self::assertTrue($this->failCalled);
		self::assertEquals('photo_url is not a valid URL', $this->failMessage);
	}

	/**
	 * Test validation with malformed URL that causes parse_url to throw an exception.
	 */
	public function testUrlParsingException(): void
	{
		$this->rule->validate('photo_url', 'http://example.com:port', fn ($m) => $this->m($m));
		self::assertTrue($this->failCalled);
		self::assertEquals('photo_url is not a valid URL', $this->failMessage);
	}

	/**
	 * Test validation when HTTPS is required but not provided.
	 */
	public function testHttpsRequiredButNotProvided(): void
	{
		$this->rule->validate('photo_url', 'http://example.com', fn ($m) => $this->m($m));
		self::assertTrue($this->failCalled);
		self::assertEquals('photo_url must be a valid HTTPS URL.', $this->failMessage);
	}

	/**
	 * Test validation with allowed HTTPS scheme when required.
	 */
	public function testHttpsRequiredAndProvided(): void
	{
		$this->rule->validate('photo_url', 'https://example.com', fn ($m) => $this->m($m));
		self::assertFalse($this->failCalled);
		self::assertEquals('', $this->failMessage);
	}

	/**
	 * Test validation with unsupported URL scheme.
	 */
	public function testUnsupportedScheme(): void
	{
		Configs::set('import_via_url_require_https', '0');

		$this->rule->validate('photo_url', 'ftp://example.com', fn ($m) => $this->m($m));
		self::assertTrue($this->failCalled);
		self::assertEquals('photo_url must be a valid HTTP or HTTPS URL.', $this->failMessage);
	}

	/**
	 * Test validation when forbidden ports are configured and non-standard port is used.
	 */
	public function testForbiddenPort(): void
	{
		$this->rule->validate('photo_url', 'https://example.com:8080', fn ($m) => $this->m($m));
		self::assertTrue($this->failCalled);
		self::assertEquals('photo_url must use a valid port such as 80 or 443.', $this->failMessage);
	}

	/**
	 * Test validation when forbidden ports are configured but standard port is used.
	 */
	public function testAllowedPort(): void
	{
		$this->rule->validate('photo_url', 'https://example.com:80', function ($message) {
			$this->failCalled = true;
		});

		self::assertFalse($this->failCalled);
		self::assertEquals('', $this->failMessage);
	}

	/**
	 * Test validation when private IP addresses are forbidden and private IP is provided.
	 */
	public function testForbiddenPrivateIp(): void
	{
		$this->rule->validate('photo_url', 'https://192.168.1.1', fn ($m) => $this->m($m));
		self::assertTrue($this->failCalled);
		self::assertEquals('photo_url must not be a private IP address.', $this->failMessage);
	}

	/**
	 * Test validation when localhost is forbidden and localhost is provided.
	 */
	public function testForbiddenLocalhost(): void
	{
		$this->rule->validate('photo_url', 'https://localhost', fn ($m) => $this->m($m));
		self::assertTrue($this->failCalled);
		self::assertEquals('photo_url must not be localhost.', $this->failMessage);
	}

	/**
	 * Test validation with a valid URL when all restrictions are disabled.
	 */
	public function testValidUrlWithNoRestrictionsWrongScheme(): void
	{
		Configs::set('import_via_url_require_https', '0');
		Configs::set('import_via_url_forbidden_ports', '0');
		Configs::set('import_via_url_forbidden_local_ip', '0');
		Configs::set('import_via_url_forbidden_localhost', '0');

		$this->rule->validate('photo_url', 'ssh://192.168.0.1:5432', fn ($m) => $this->m($m));

		self::assertTrue($this->failCalled);
		self::assertEquals('photo_url must be a valid HTTP or HTTPS URL.', $this->failMessage);
	}

	/**
	 * Test validation with a valid URL when all restrictions are disabled.
	 */
	public function testValidUrlWithNoRestrictionsCorrectScheme(): void
	{
		Configs::set('import_via_url_require_https', '0');
		Configs::set('import_via_url_forbidden_ports', '0');
		Configs::set('import_via_url_forbidden_local_ip', '0');
		Configs::set('import_via_url_forbidden_localhost', '0');

		$this->rule->validate('photo_url', 'http://192.168.0.1:5432', fn ($m) => $this->m($m));

		self::assertFalse($this->failCalled);
		self::assertEquals('', $this->failMessage);
	}
}
