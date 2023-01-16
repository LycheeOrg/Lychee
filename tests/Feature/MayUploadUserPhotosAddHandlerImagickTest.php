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

use Tests\Feature\Abstract\AbstractPhotosAddHandlerImagickTest;
use Tests\Feature\Traits\ExecuteAsMayUploadUser;

/**
 * Runs the tests as MayUploadUser of {@link PhotosAddHandlerTestAbstract} with Imagick as image handler.
 */
class MayUploadUserPhotosAddHandlerImagickTest extends AbstractPhotosAddHandlerImagickTest
{
	use ExecuteAsMayUploadUser;
}
