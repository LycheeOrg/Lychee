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

namespace Tests\Feature_v2\Frame;

use Tests\Feature_v2\Base\BaseApiV2Test;

class FrameTest extends BaseApiV2Test
{
	public function testErrors(): void
	{
		$response = $this->getJson('Frame');
		$this->assertUnprocessable($response);
		$response->assertJson([
			'message' => 'The album id field must be present.',
		]);

		$response = $this->getJsonWithData('Frame', ['album_id' => null]);
		$this->assertUnauthorized($response);

		$response = $this->actingAs($this->admin)->getJsonWithData('Frame', ['album_id' => null]);
		$this->assertInternalServerError($response);
	}

	public function testGet(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJsonWithData('Frame', ['album_id' => $this->album1->id]);
		$this->assertOk($response);
		$response->assertJson([
			'timeout' => 30,
			'src' => $this->photo1->size_variants->getMedium()->url,
		]);
	}
}