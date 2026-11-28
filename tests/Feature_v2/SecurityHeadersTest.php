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

namespace Tests\Feature_v2;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

/**
 * API responses carry a small header set, so they fit a stock nginx
 * `proxy_buffer_size` of 4 KB (discussion #4901).
 */
class SecurityHeadersTest extends BaseApiWithDataTest
{
	public function testApiResponseCarriesMinimalHeaders(): void
	{
		$response = $this->getJson('Auth::config');
		$this->assertOk($response);

		self::assertStringStartsWith("default-src 'none'; frame-ancestors ", $response->headers->get('Content-Security-Policy'));
		self::assertFalse($response->headers->has('Permissions-Policy'));
	}

	public function testEmbedResponseCarriesMinimalHeaders(): void
	{
		$response = $this->getJson('Embed/' . $this->album1->id);

		self::assertStringStartsWith("default-src 'none'; frame-ancestors ", $response->headers->get('Content-Security-Policy'));
		self::assertFalse($response->headers->has('Permissions-Policy'));
	}

	public function testPagesKeepThePagePolicy(): void
	{
		$response = $this->get('/gallery');
		$this->assertOk($response);

		self::assertSame('geolocation=(), midi=()', $response->headers->get('Permissions-Policy'));
	}

	public function testRememberMeLoginFitsTheNginxDefaultHeaderBuffer(): void
	{
		$response = $this->postJson('Auth::login', [
			'username' => $this->admin->username,
			'password' => 'password',
			'remember_me' => true,
		]);
		$this->assertNoContent($response);

		// A controller returning nothing is still an API response, not a page.
		self::assertStringStartsWith("default-src 'none'; frame-ancestors ", $response->headers->get('Content-Security-Policy'));
		self::assertFalse($response->headers->has('Permissions-Policy'));

		// Keep 1 KB of the 4 KB buffer for `secure` cookie flags, HSTS and the proxy's own headers.
		self::assertLessThan(3072, self::wireSize($response->baseResponse->headers));
	}

	/**
	 * Size of the headers as sent: `Name: value\r\n` per line, cookies included.
	 */
	private static function wireSize(ResponseHeaderBag $headers): int
	{
		$size = 0;
		foreach ($headers->allPreserveCaseWithoutCookies() as $name => $values) {
			foreach ($values as $value) {
				$size += strlen($name . ': ' . $value . "\r\n");
			}
		}
		foreach ($headers->getCookies() as $cookie) {
			$size += strlen('Set-Cookie: ' . $cookie . "\r\n");
		}

		return $size;
	}
}
