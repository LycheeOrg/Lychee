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

namespace Tests\Feature_v2;

use Tests\Feature_v2\Base\BaseV2Test;

class PagesTest extends BaseV2Test
{
	public function testIndex(): void
	{
		collect([
			'/',
			'/settings',
			'/diagnostics',
			'/jobs',
			'/sharing',
			'/users',
			'/maintenance',
			'/profile',
			'/gallery',
			'/gallery/' . $this->album4->id,
			'/gallery/' . $this->album4->id . '/' . $this->photo4->id,
			'/frame',
			'/frame/' . $this->album4->id,
			'/map',
			'/map/' . $this->album4->id,
			'/search',
			'/search/' . $this->album4->id,
			'/search/' . $this->album4->id . '/' . $this->photo4->id,
		])->each(function ($addr) {
			$response = $this->get($addr);
			$this->assertOk($response);
			$response->assertViewIs('vueapp');
		});
	}
}