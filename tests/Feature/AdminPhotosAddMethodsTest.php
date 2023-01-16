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

use Tests\Feature\Abstract\AbstractPhotosAddMethodsTest;
use Tests\Feature\Traits\ExecuteAsAdmin;

/**
 * Contains all tests as Admin for the various ways of adding images to Lychee
 * (upload, download, import) and their various options.
 */
class AdminPhotosAddMethodsTest extends AbstractPhotosAddMethodsTest
{
	use ExecuteAsAdmin;
}
