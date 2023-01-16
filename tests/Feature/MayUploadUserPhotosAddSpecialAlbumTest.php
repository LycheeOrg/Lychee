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

use Tests\Feature\Abstract\AbstractPhotosAddSpecialAlbumTest;
use Tests\Feature\Traits\ExecuteAsMayUploadUser;

/**
 * Contains tests as MayUploadUser which add photos to Lychee and directly set an album.
 */
class MayUploadUserPhotosAddSpecialAlbumTest extends AbstractPhotosAddSpecialAlbumTest
{
	use ExecuteAsMayUploadUser;
}
