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

namespace Tests\Feature\Base;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\AbstractTestCase;
use Tests\Feature\Lib\AlbumsUnitTest;
use Tests\Feature\Lib\PhotosUnitTest;
use Tests\Feature\Traits\RequiresAdmin;
use Tests\Feature\Traits\RequiresEmptyPhotos;
use Tests\Feature\Traits\RequiresExifTool;
use Tests\Feature\Traits\RequiresFFMpeg;

abstract class BasePhotoTest extends AbstractTestCase
{
	use RequiresEmptyPhotos;
	use RequiresExifTool;
	use RequiresFFMpeg;
	use RequiresAdmin;

	protected AlbumsUnitTest $albums_tests;
	protected PhotosUnitTest $photos_tests;

	public function setUp(): void
	{
		parent::setUp();
		$this->albums_tests = new AlbumsUnitTest($this);
		$this->photos_tests = new PhotosUnitTest($this);
		$this->setUpRequiresExifTool();
		$this->setUpRequiresFFMpeg();
		$this->setUpRequiresEmptyPhotos();
		Auth::loginUsingId($this->executeAs());
	}

	public function tearDown(): void
	{
		Auth::logout();
		Session::flush();
		$this->tearDownRequiresEmptyPhotos();
		$this->tearDownRequiresFFMpeg();
		$this->tearDownRequiresExifTool();
		$this->logoutAs();
		parent::tearDown();
	}

	/**
	 * Allow selection of which user to log in with.
	 *
	 * @return int user ID
	 */
	abstract protected function executeAs(): int;

	/**
	 * Because we can use executeAs() to create an extra user.
	 * It is also necessary to be able to remove it.
	 *
	 * @return void
	 */
	abstract protected function logoutAs(): void;
}
