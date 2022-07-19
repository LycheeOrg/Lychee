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

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Tests\Feature\Lib\AlbumsUnitTest;
use Tests\TestCase;

class SharingTest extends TestCase
{
	protected AlbumsUnitTest $albums_tests;

	public function setUp(): void
	{
		parent::setUp();
		$this->albums_tests = new AlbumsUnitTest($this);
	}

	/**
	 * @return void
	 */
	public function testSharing(): void
	{
		Auth::loginUsingId(0);

		$albumID1 = $this->albums_tests->add(null, 'test_album')->offsetGet('id');
		$albumID2 = $this->albums_tests->add($albumID1, 'test_album2')->offsetGet('id');

		$response = $this->postJson('/api/Sharing::list');
		$response->assertStatus(200);

		$this->albums_tests->delete([$albumID1, $albumID2]);

		Auth::logout();
		Session::flush();
	}
}
