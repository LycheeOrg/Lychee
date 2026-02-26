<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
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

namespace Tests\Feature_v2\Photo;

use Tests\Feature_v2\Base\BaseApiWithDataTest;

class PhotoBulkLicenseTest extends BaseApiWithDataTest
{
	public function testSetLicenseUnauthorized(): void
	{
		$response = $this->patchJson('Photo::license', [
			'photo_ids' => [$this->photo1->id],
			'license' => 'CC-BY-4.0',
		]);
		$this->assertUnauthorized($response);
	}

	public function testSetLicenseForbidden(): void
	{
		$response = $this->actingAs($this->userNoUpload)->patchJson('Photo::license', [
			'photo_ids' => [$this->photo1->id],
			'license' => 'CC-BY-4.0',
		]);
		$this->assertForbidden($response);
	}

	public function testSetLicenseValidationErrors(): void
	{
		// Missing photo_ids
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo::license', [
			'license' => 'CC-BY-4.0',
		]);
		$this->assertUnprocessable($response);

		// Empty photo_ids array
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo::license', [
			'photo_ids' => [],
			'license' => 'CC-BY-4.0',
		]);
		$this->assertUnprocessable($response);

		// Invalid photo ID
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo::license', [
			'photo_ids' => ['invalid-id'],
			'license' => 'CC-BY-4.0',
		]);
		$this->assertUnprocessable($response);

		// Missing license
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo::license', [
			'photo_ids' => [$this->photo1->id],
		]);
		$this->assertUnprocessable($response);

		// Invalid license value
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo::license', [
			'photo_ids' => [$this->photo1->id],
			'license' => 'invalid-license',
		]);
		$this->assertUnprocessable($response);
	}

	public function testSetLicenseSinglePhoto(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo::license', [
			'photo_ids' => [$this->photo1->id],
			'license' => 'CC-BY-4.0',
		]);
		$this->assertNoContent($response);

		// Verify the license was updated
		$this->photo1->refresh();
		$this->assertSame('CC-BY-4.0', $this->photo1->license->value);
	}

	public function testSetLicenseMultiplePhotos(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo::license', [
			'photo_ids' => [$this->photo1->id, $this->photo1b->id],
			'license' => 'CC-BY-SA-4.0',
		]);
		$this->assertNoContent($response);

		// Verify both photos were updated
		$this->photo1->refresh();
		$this->photo1b->refresh();
		$this->assertSame('CC-BY-SA-4.0', $this->photo1->license->value);
		$this->assertSame('CC-BY-SA-4.0', $this->photo1b->license->value);
	}

	public function testSetLicenseAllLicenseTypes(): void
	{
		$licenseTypes = [
			'none',
			'reserved',
			'CC0',
			'CC-BY-4.0',
			'CC-BY-ND-4.0',
			'CC-BY-SA-4.0',
			'CC-BY-NC-4.0',
			'CC-BY-NC-ND-4.0',
			'CC-BY-NC-SA-4.0',
		];

		foreach ($licenseTypes as $license) {
			$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo::license', [
				'photo_ids' => [$this->photo1->id],
				'license' => $license,
			]);
			$this->assertNoContent($response);

			$this->photo1->refresh();
			$this->assertSame($license, $this->photo1->license->value);
		}
	}

	public function testSetLicenseMixedOwnership(): void
	{
		// userMayUpload1 owns photo1 but not photo3 (owned by userNoUpload)
		$response = $this->actingAs($this->userMayUpload1)->patchJson('Photo::license', [
			'photo_ids' => [$this->photo1->id, $this->photo3->id],
			'license' => 'CC-BY-4.0',
		]);
		$this->assertForbidden($response);
	}
}
