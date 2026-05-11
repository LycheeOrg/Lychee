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

namespace Tests\Unit\Services;

use App\Models\Configs;
use App\Repositories\ConfigManager;
use App\Services\UrlValidation;
use Tests\AbstractTestCase;

class UrlValidationTest extends AbstractTestCase
{
	private UrlValidation $url_validation;

	public function setUp(): void
	{
		parent::setUp();
		$this->url_validation = $this->makeUrlValidation();

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

	/**
	 * @param \Closure|null $dns_get_record
	 */
	private function makeUrlValidation(?\Closure $dns_get_record = null): UrlValidation
	{
		return new UrlValidation(
			resolve(ConfigManager::class),
			$dns_get_record ?? fn (string $hostname, int $type = DNS_A) => [],
		);
	}

	public function testNonStringValue(): void
	{
		$dto = $this->url_validation->validate(123);

		self::assertEquals('', $dto->url);
		self::assertNull($dto->resolved_ip);
		self::assertEquals('is not a string.', $dto->error);
	}

	public function testNullValue(): void
	{
		$dto = $this->url_validation->validate(null);

		self::assertEquals('', $dto->url);
		self::assertNull($dto->resolved_ip);
		self::assertEquals('is not a string.', $dto->error);
	}

	public function testInvalidUrlFormat(): void
	{
		$dto = $this->url_validation->validate('not-a-url');

		self::assertEquals('not-a-url', $dto->url);
		self::assertNull($dto->resolved_ip);
		self::assertEquals('is not a valid URL.', $dto->error);
	}

	public function testHttpsRequiredButNotProvided(): void
	{
		$dto = $this->url_validation->validate('http://example.com');

		self::assertEquals('must be a valid HTTPS URL.', $dto->error);
	}

	public function testUnsupportedScheme(): void
	{
		Configs::set('import_via_url_require_https', '0');

		$dto = $this->url_validation->validate('ftp://example.com');

		self::assertEquals('must be a valid HTTP or HTTPS URL.', $dto->error);
	}

	public function testForbiddenPort(): void
	{
		$dto = $this->url_validation->validate('https://example.com:8080');

		self::assertEquals('must use a valid port such as 80 or 443.', $dto->error);
	}

	public function testAllowedPort(): void
	{
		$this->url_validation = $this->makeUrlValidation(fn (string $hostname, int $type = DNS_A) => match ($type) {
			DNS_A => [['ip' => '93.184.216.34']],
			default => [],
		});

		$dto = $this->url_validation->validate('https://example.com:80');

		self::assertNull($dto->error);
		self::assertEquals('93.184.216.34', $dto->resolved_ip);
	}

	public function testForbiddenPrivateIp(): void
	{
		$dto = $this->url_validation->validate('https://192.168.1.1');

		self::assertEquals('must not resolve to a private or reserved IP address.', $dto->error);
	}

	public function testForbiddenLocalhost(): void
	{
		Configs::set('import_via_url_forbidden_local_ip', '0');

		$dto = $this->url_validation->validate('https://localhost');

		self::assertEquals('must not resolve to localhost.', $dto->error);
	}

	public function testForbiddenPrivateIpViaHostname(): void
	{
		$this->url_validation = $this->makeUrlValidation(fn (string $hostname, int $type = DNS_A) => match ($type) {
			DNS_A => [['ip' => '192.168.0.1']],
			default => [],
		});

		$dto = $this->url_validation->validate('https://evil.example.com/test.jpg');

		self::assertEquals('must not resolve to a private or reserved IP address.', $dto->error);
	}

	public function testForbiddenLocalhostViaHostnameIpv4(): void
	{
		$this->url_validation = $this->makeUrlValidation(fn (string $hostname, int $type = DNS_A) => match ($type) {
			DNS_A => [['ip' => '127.0.0.1']],
			default => [],
		});
		Configs::set('import_via_url_forbidden_local_ip', '0');

		$dto = $this->url_validation->validate('https://evil.example.com/test.jpg');

		self::assertEquals('must not resolve to localhost.', $dto->error);
	}

	public function testForbiddenLocalhostViaHostnameIpv6(): void
	{
		$this->url_validation = $this->makeUrlValidation(fn (string $hostname, int $type = DNS_AAAA) => match ($type) {
			DNS_AAAA => [['ipv6' => '::1']],
			default => [],
		});
		Configs::set('import_via_url_forbidden_local_ip', '0');

		$dto = $this->url_validation->validate('https://evil.example.com/test.jpg');

		self::assertEquals('must not resolve to localhost.', $dto->error);
	}

	public function testValidUrlWithNoRestrictionsCorrectScheme(): void
	{
		Configs::set('import_via_url_require_https', '0');
		Configs::set('import_via_url_forbidden_ports', '0');
		Configs::set('import_via_url_forbidden_local_ip', '0');
		Configs::set('import_via_url_forbidden_localhost', '0');

		$dto = $this->url_validation->validate('http://192.168.0.1:5432');

		self::assertNull($dto->error);
		self::assertEquals('192.168.0.1', $dto->resolved_ip);
	}

	public function testValidUrlWithNoRestrictionsAndUnresolvedHost(): void
	{
		Configs::set('import_via_url_require_https', '0');
		Configs::set('import_via_url_forbidden_ports', '0');
		Configs::set('import_via_url_forbidden_local_ip', '0');
		Configs::set('import_via_url_forbidden_localhost', '0');

		$dto = $this->url_validation->validate('http://example.com:5432');

		self::assertNull($dto->error);
		self::assertNull($dto->resolved_ip);
	}
}
