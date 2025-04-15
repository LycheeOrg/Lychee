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

namespace Tests\Feature_v2;

use App\Http\Controllers\VersionController;
use App\Http\Resources\Diagnostics\ChangeLogInfo;
use Tests\Feature_v2\Base\BaseApiV2Test;

class VersionTest extends BaseApiV2Test
{
	public function testGet(): void
	{
		$response = $this->getJson('Version');
		$this->assertOk($response);
	}

	public function testGetChangeLogs(): void
	{
		$response = $this->getJson('ChangeLogs');
		$this->assertOk($response);
		foreach ($response->json() as $changelog) {
			$this->assertArrayHasKey('version', $changelog);
			$this->assertArrayHasKey('date', $changelog);
			$this->assertArrayHasKey('changes', $changelog);
			$this->assertNotEmpty($changelog['version']);
			$this->assertNotEmpty($changelog['date']);
			$this->assertNotEmpty($changelog['changes']);
		}
	}

	public function testConversion(): void
	{
		$overloaded_controller = new class() extends VersionController {
			public function public_convert(string $response): array
			{
				return $this->convert($response);
			}
		};

		$test_string = '
<style>
test
</style>

## Version 6

### v6.4.2

Released on Apr 4, 2025

#### New Settings page & translations (French, Russian)

`klo` refers to *Keep the Light On*. In other words, basic software updates.  
`SE` refers to functionalities that are aimed at the Supporter Edition.

* `new` #3081 : Refactoring Settings page by @ildyria.

## Version 5

### v5.5.1

Released on Jul 5, 2024

#### Changes

* `fixes` #2487 : Fixes videos not loading from S3 due to unlisted CSP host by @RickyRomero.
* `new` #2490 : Add support for paths in php-exif by @ildyria.
* `fixes` #2492 : Fix error when opening tag album by @ildyria.
* `klo` #2493 : Simplify by @ildyria.
';
		$expected = [
			new ChangeLogInfo(
				'6.4.2',
				'Released on Apr 4, 2025',
				"<h4>New Settings page &amp; translations (French, Russian)</h4>\n<p><code>klo</code> refers to <em>Keep the Light On</em>. In other words, basic software updates.<br />\n<code>SE</code> refers to functionalities that are aimed at the Supporter Edition.</p>\n<ul>\n<li><code>new</code> <a href=\"https://github.com/LycheeOrg/Lychee/pull/3081\">#3081</a> : Refactoring Settings page by @ildyria.</li>\n</ul>\n"
			),
			new ChangeLogInfo(
				'5.5.1',
				'Released on Jul 5, 2024',
				"<h4>Changes</h4>\n<ul>\n<li><code>fixes</code> <a href=\"https://github.com/LycheeOrg/Lychee/pull/2487\">#2487</a> : Fixes videos not loading from S3 due to unlisted CSP host by @RickyRomero.</li>\n<li><code>new</code> <a href=\"https://github.com/LycheeOrg/Lychee/pull/2490\">#2490</a> : Add support for paths in php-exif by @ildyria.</li>\n<li><code>fixes</code> <a href=\"https://github.com/LycheeOrg/Lychee/pull/2492\">#2492</a> : Fix error when opening tag album by @ildyria.</li>\n<li><code>klo</code> <a href=\"https://github.com/LycheeOrg/Lychee/pull/2493\">#2493</a> : Simplify by @ildyria.</li>\n</ul>\n"
			),
		];

		self::assertEquals($expected, $overloaded_controller->public_convert($test_string));
	}
}