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

namespace Tests\Feature;

use Tests\Feature\Base\BasePhotosRotateTest;
use Tests\Feature\Traits\ExecuteAsAdmin;
use Tests\Feature\Traits\RequiresImageHandler;

/**
 * Runs the tests of {@link BasePhotosRotateTest} with Imagick as image handler.
 */
class AdminPhotosRotateImagickTest extends BasePhotosRotateTest
{
	use RequiresImageHandler;
	use ExecuteAsAdmin;

	public function setUp(): void
	{
		parent::setUp();
		$this->setUpRequiresImagick();
	}

	public function tearDown(): void
	{
		$this->tearDownRequiresImageHandler();
		parent::tearDown();
	}
}
