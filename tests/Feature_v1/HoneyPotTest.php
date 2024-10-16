<?php

/**
 * We don't care for unhandled exceptions in tests.
 * It is the nature of a test to throw an exception.
 * Without this suppression we had 100+ Linter warning in this file which
 * don't help anything.
 *
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\Feature_v1;

use Illuminate\Http\Response;
use Tests\AbstractTestCase;

class HoneyPotTest extends AbstractTestCase
{
	public function testRoutesWithHoney(): void
	{
		foreach (config('honeypot.paths') as $path) {
			$response = $this->get($path);
			$this->assertStatus($response, Response::HTTP_I_AM_A_TEAPOT);
			$response = $this->post($path);
			$this->assertStatus($response, Response::HTTP_I_AM_A_TEAPOT);
		}

		// We check one of the version from the xpaths cross product
		$response = $this->get('admin.asp');
		$this->assertStatus($response, Response::HTTP_I_AM_A_TEAPOT);
	}

	public function testRoutesWithoutHoney(): void
	{
		$response = $this->get('/something');
		$this->assertStatus($response, Response::HTTP_NOT_FOUND);
	}

	public function testDisabled(): void
	{
		config(['honeypot.enabled' => false]);
		foreach (config('honeypot.paths') as $path) {
			$response = $this->get($path);
			$this->assertStatus($response, Response::HTTP_NOT_FOUND);
			$response = $this->post($path);
			$this->assertStatus($response, Response::HTTP_NOT_FOUND);
		}
	}
}