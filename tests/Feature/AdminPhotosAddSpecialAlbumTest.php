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
use Tests\Feature\Traits\ExecuteAsAdmin;

/**
 * Contains tests as Admin which add photos to Lychee and directly set an album.
 */
class AdminPhotosAddSpecialAlbumTest extends AbstractPhotosAddSpecialAlbumTest
{
	use ExecuteAsAdmin;
}
