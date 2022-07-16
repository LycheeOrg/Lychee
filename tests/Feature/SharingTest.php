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

use App\Facades\AccessControl;
use Tests\Feature\Base\PhotoTestBase;

class SharingTest extends PhotoTestBase
{
	/**
	 * @return void
	 */
	public function testEmptySharingList(): void
	{
		AccessControl::log_as_id(0);

		$response = $this->postJson('/api/Sharing::list');
		$response->assertStatus(200);
		$response->assertExactJson([
			'shared' => [],
			'albums' => [],
			'users' => [],
		]);

		AccessControl::logout();
	}

	/**
	 * @return void
	 */
	public function testSharingListWithAlbums(): void
	{
		AccessControl::log_as_id(0);

		$albumID1 = $this->albums_tests->add(null, 'test_album')->offsetGet('id');
		$albumID2 = $this->albums_tests->add($albumID1, 'test_album2')->offsetGet('id');

		$response = $this->postJson('/api/Sharing::list');
		$response->assertStatus(200);
		$response->assertSimilarJson([
			'shared' => [],
			'albums' => [[
				'id' => $albumID1,
				'title' => 'test_album',
			], [
				'id' => $albumID2,
				'title' => 'test_album/test_album2',
			]],
			'users' => [],
		]);

		$this->albums_tests->delete([$albumID1, $albumID2]);

		AccessControl::logout();
	}
}
