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
			'/search',
			'/map',
			'/settings',
			'/diagnostics',
			'/jobs',
			'/sharing',
			'/users',
			'/maintenance',
			'/frame',
			'/profile',
			'/gallery',
			'/gallery/albumID',
			'/gallery/albumID/photoID',
		])->each(function ($addr) {
			$response = $this->get($addr);
			$this->assertOk($response);
			$response->assertViewIs('vueapp');
		});
	}
}