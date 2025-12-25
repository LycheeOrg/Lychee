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

namespace Tests\Unit\Http\Controllers;

use App\Enum\StorageDiskType;
use App\Exceptions\SecurePaths\InvalidPayloadException;
use App\Exceptions\SecurePaths\PathTraversalException;
use App\Exceptions\SecurePaths\WrongPathException;
use App\Http\Controllers\SecurePathController;
use App\Http\Requests\SecurePath\SecurePathRequest;
use App\Models\Configs;
use App\Repositories\ConfigManager;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\AbstractTestCase;

/**
 * Unit tests for SecurePathController.
 *
 * Tests the secure file serving functionality including:
 * - Signature validation
 * - Expiration checking
 * - Path traversal protection
 * - Encryption/decryption of paths
 * - File existence checks
 */
class SecurePathControllerTest extends AbstractTestCase
{
	use DatabaseTransactions;

	private SecurePathController $controller;

	public function setUp(): void
	{
		parent::setUp();
		$this->controller = new SecurePathController();
	}

	/**
	 * Create a mock SecurePathRequest for testing.
	 */
	private function createMockSecurePathRequest()
	{
		return new class() extends SecurePathRequest {
			public function configs()
			{
				return resolve(ConfigManager::class);
			}

			public function authorize(): bool
			{
				return true; // Always authorize for tests
			}

			public function hasValidSignature(): bool
			{
				return true; // Always valid signature for basic tests
			}

			public function query($key = null, $default = null)
			{
				// Return future timestamp for expires
				if ($key === 'expires') {
					return (string) Carbon::now()->addHour()->getTimestamp();
				}

				return $default;
			}

			public function url(): string
			{
				return 'http://test.com/secure';
			}

			public function __get($name)
			{
				if ($name === 'server') {
					return (object) ['get' => fn () => 'test=1'];
				}

				return null;
			}
		};
	}

	/**
	 * Test exception when path is null.
	 */
	public function testWrongPathExceptionWhenPathIsNull(): void
	{
		// Mock configs to disable signature checking
		Configs::set('temporary_image_link_enabled', false);
		Configs::set('secure_image_link_enabled', false);
		Config::set('features.populate-request-macros', true);

		$this->expectException(WrongPathException::class);

		// Create a mock request that passes signature validation
		$request = $this->createMockSecurePathRequest();

		$this->controller->__invoke($request, null);
	}

	/**
	 * Test path decryption failure when secure_image_link_enabled is true.
	 */
	public function testInvalidPayloadExceptionWhenDecryptionFails(): void
	{
		// Mock configs
		Configs::set('temporary_image_link_enabled', false);
		Configs::set('secure_image_link_enabled', true);

		$this->expectException(InvalidPayloadException::class);

		$request = $this->createMockSecurePathRequest();
		$invalidEncryptedPath = 'invalid-encrypted-string';

		$this->controller->__invoke($request, $invalidEncryptedPath);
	}

	/**
	 * Test path traversal protection.
	 */
	public function testPathTraversalException(): void
	{
		// Mock configs
		Configs::set('temporary_image_link_enabled', false);
		Configs::set('secure_image_link_enabled', false);
		Config::set('features.populate-request-macros', true);

		// Mock storage to simulate path traversal
		$maliciousPath = '../../../etc/passwd';
		$validAppPath = storage_path('app/');
		$maliciousFullPath = '/etc/passwd'; // Outside app directory

		Storage::shouldReceive('disk')
			->with(StorageDiskType::LOCAL->value)
			->andReturnSelf();

		Storage::shouldReceive('path')
			->with($maliciousPath)
			->andReturn($maliciousFullPath);

		Storage::shouldReceive('path')
			->with('')
			->andReturn($validAppPath);

		$this->expectException(PathTraversalException::class);

		$request = $this->createMockSecurePathRequest();
		$this->controller->__invoke($request, $maliciousPath);
	}

	/**
	 * Test exception when file doesn't exist.
	 */
	public function testWrongPathExceptionWhenFileDoesNotExist(): void
	{
		// Mock configs
		Configs::set('temporary_image_link_enabled', false);
		Configs::set('secure_image_link_enabled', false);
		Config::set('features.populate-request-macros', true);

		$nonExistentPath = 'test/nonexistent.jpg';
		$fullPath = storage_path('app/' . $nonExistentPath);

		Storage::shouldReceive('disk')
			->with(StorageDiskType::LOCAL->value)
			->andReturnSelf();

		Storage::shouldReceive('path')
			->with($nonExistentPath)
			->andReturn($fullPath);

		Storage::shouldReceive('path')
			->with('')
			->andReturn(storage_path('app/'));

		$this->expectException(WrongPathException::class);

		$request = $this->createMockSecurePathRequest();
		$this->controller->__invoke($request, $nonExistentPath);
	}

	/**
	 * Test signatureHasNotExpired method with expired timestamp.
	 */
	public function testSignatureHasNotExpiredWithExpiredTimestamp(): void
	{
		$expiredTimestamp = Carbon::now()->subHour()->getTimestamp();
		$request = Request::create('/', 'GET', ['expires' => (string) $expiredTimestamp]);

		// Use reflection to access private method
		$reflection = new \ReflectionClass(SecurePathController::class);
		$method = $reflection->getMethod('signatureHasNotExpired');
		$result = $method->invoke($this->controller, $request);

		$this->assertFalse($result);
	}

	/**
	 * Test signatureHasNotExpired method with valid timestamp.
	 */
	public function testSignatureHasNotExpiredWithValidTimestamp(): void
	{
		$futureTimestamp = Carbon::now()->addHour()->getTimestamp();
		$request = Request::create('/', 'GET', ['expires' => (string) $futureTimestamp]);

		// Use reflection to access private method
		$reflection = new \ReflectionClass(SecurePathController::class);
		$method = $reflection->getMethod('signatureHasNotExpired');

		$result = $method->invoke($this->controller, $request);

		$this->assertTrue($result);
	}

	/**
	 * Test signatureHasNotExpired method with no expires parameter.
	 */
	public function testSignatureHasNotExpiredWithNoExpiresParameter(): void
	{
		$request = Request::create('/', 'GET');

		// Use reflection to access private method
		$reflection = new \ReflectionClass(SecurePathController::class);
		$method = $reflection->getMethod('signatureHasNotExpired');

		$result = $method->invoke($this->controller, $request);

		$this->assertTrue($result); // Should return true when no expires parameter
	}

	/**
	 * Test getUrl method builds correct URL without signature parameter.
	 */
	public function testGetUrlMethodRemovesSignatureParameter(): void
	{
		$request = Request::create('http://test.com/secure/path', 'GET', [
			'expires' => '1234567890',
			'signature' => 'test-signature',
			'other_param' => 'value',
		]);

		// Use reflection to access private method
		$reflection = new \ReflectionClass(SecurePathController::class);
		$method = $reflection->getMethod('getUrl');

		$result = $method->invoke($this->controller, $request);

		$this->assertEquals('http://test.com/secure/path?expires=1234567890&other_param=value', $result);
	}
}