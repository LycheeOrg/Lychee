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

use Tests\Feature\Abstract\AbstractPhotosAddHandlerGDTest;
use Tests\Feature\Traits\ExecuteAsMayUploadUser;

/**
 * Runs the tests as MayUploadUser of {@link BasePhotosAddHandler} with GD as image handler.
 */
class MayUploadUserPhotosAddHandlerGDTest extends AbstractPhotosAddHandlerGDTest
{
	use ExecuteAsMayUploadUser;
}
