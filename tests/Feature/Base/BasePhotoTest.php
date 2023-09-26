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

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\AbstractTestCase;
use Tests\Feature\LibUnitTests\AlbumsUnitTest;
use Tests\Feature\LibUnitTests\PhotosUnitTest;
use Tests\Feature\Traits\RequiresEmptyPhotos;
use Tests\Feature\Traits\RequiresExifTool;
use Tests\Feature\Traits\RequiresFFMpeg;

abstract class BasePhotoTest extends AbstractTestCase
{
	use RequiresEmptyPhotos;
	use RequiresExifTool;
	use RequiresFFMpeg;
	use DatabaseTransactions;

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
		Auth::loginUsingId(1);
	}

	public function tearDown(): void
	{
		Auth::logout();
		Session::flush();
		$this->tearDownRequiresEmptyPhotos();
		$this->tearDownRequiresFFMpeg();
		$this->tearDownRequiresExifTool();
		parent::tearDown();
	}
}
