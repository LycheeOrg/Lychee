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

use Tests\Feature\Abstract\AbstractPhotosAddNegativeTest;
use Tests\Feature\Traits\ExecuteAsMayUploadUser;

/**
 * Contains all tests which add photos to Lychee and are expected to fail as MayUploadUser.
 */
class MayUploadUserPhotosAddNegativeTest extends AbstractPhotosAddNegativeTest
{
	use ExecuteAsMayUploadUser;
}
