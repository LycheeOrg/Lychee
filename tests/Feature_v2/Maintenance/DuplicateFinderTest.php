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

namespace Tests\Feature_v2\Maintenance;

use Tests\Feature_v2\Base\BaseApiV2Test;

class DuplicateFinderTest extends BaseApiV2Test
{
	public function testGuest(): void
	{
		$response = $this->getJsonWithData('Maintenance::countDuplicates');
		$this->assertUnauthorized($response);

		$response = $this->getJsonWithData('Maintenance::searchDuplicates');
		$this->assertUnprocessable($response);

		$response = $this->getJsonWithData('Maintenance::searchDuplicates', [
			'with_album_constraint' => false,
			'with_checksum_constraint' => true,
			'with_title_constraint' => false,
		]);
		$this->assertUnauthorized($response);

		$response = $this->getJsonWithData('Maintenance::searchDuplicates', [
			'with_album_constraint' => true,
			'with_checksum_constraint' => false,
			'with_title_constraint' => true,
		]);
		$this->assertUnprocessable($response);
	}

	public function testUser(): void
	{
		$response = $this->actingAs($this->userLocked)->getJsonWithData('Maintenance::countDuplicates');
		$this->assertForbidden($response);

		$response = $this->actingAs($this->userLocked)->getJsonWithData('Maintenance::searchDuplicates');
		$this->assertUnprocessable($response);

		$response = $this->getJsonWithData('Maintenance::searchDuplicates', [
			'with_album_constraint' => false,
			'with_checksum_constraint' => true,
			'with_title_constraint' => false,
		]);
		$this->assertForbidden($response);

		$response = $this->getJsonWithData('Maintenance::searchDuplicates', [
			'with_album_constraint' => true,
			'with_checksum_constraint' => false,
			'with_title_constraint' => true,
		]);
		$this->assertUnprocessable($response);
	}

	public function testAdmin(): void
	{
		$response = $this->actingAs($this->admin)->getJsonWithData('Maintenance::countDuplicates');
		$this->assertOk($response);
		$response->assertJson([
			'pure_duplicates' => 0,
			'title_duplicates' => 0,
			'duplicates_within_album' => 0,
		]);

		$response = $this->actingAs($this->admin)->getJsonWithData('Maintenance::searchDuplicates');
		$this->assertUnprocessable($response);

		$response = $this->getJsonWithData('Maintenance::searchDuplicates', [
			'with_album_constraint' => false,
			'with_checksum_constraint' => true,
			'with_title_constraint' => false,
		]);
		$this->assertOk($response);

		$response = $this->getJsonWithData('Maintenance::searchDuplicates', [
			'with_album_constraint' => true,
			'with_checksum_constraint' => false,
			'with_title_constraint' => true,
		]);
		$this->assertUnprocessable($response);
	}
}