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

namespace Tests\Feature_v1\Base;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\AbstractTestCase;
use Tests\Feature_v1\LibUnitTests\AlbumsUnitTest;
use Tests\Feature_v1\LibUnitTests\PhotosUnitTest;
use Tests\Traits\RequiresEmptyPhotos;
use Tests\Traits\RequiresExifTool;
use Tests\Traits\RequiresFFMpeg;

abstract class BasePhotoTest extends AbstractTestCase
{
	use RequiresEmptyPhotos;
	use RequiresExifTool;
	use RequiresFFMpeg;

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
