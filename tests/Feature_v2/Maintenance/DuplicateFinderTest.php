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

use App\Actions\Photo\DuplicateFinder;
use App\Models\Album;
use App\Models\Photo;
use Illuminate\Support\Collection;
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

	public function testActionCountDuplicates(): void
	{
		// First we create the following images:
		$album = Album::factory()->as_root()->owned_by($this->admin)->create();
		$album2 = Album::factory()->as_root()->owned_by($this->admin)->create();
		$photo1 = Photo::factory()->owned_by($this->admin)->with_checksum('1234567890abcdef')->with_title('duplicate')->in($album)->create();
		$photo2 = Photo::factory()->owned_by($this->admin)->with_checksum('1234567890abcdef')->with_title('duplicate2')->in($album)->create();
		$photo3 = Photo::factory()->owned_by($this->admin)->with_checksum('1234567890abcdef')->with_title('duplicate')->in($album2)->create();
		$photo4 = Photo::factory()->owned_by($this->admin)->with_checksum('1234567890abcde')->with_title('duplicate2')->in($album)->create();

		$response = $this->actingAs($this->admin)->getJsonWithData('Maintenance::countDuplicates');
		$this->assertOk($response);
		$response->assertJson([
			'pure_duplicates' => 3, // 3 duplicates with the same checksum
			'title_duplicates' => 2, // 2 duplicates with same title in same album
			'duplicates_within_album' => 2, // 2 duplicates with same checksum and in same album
		]);

		$response = $this->getJsonWithData('Maintenance::searchDuplicates', [
			'with_album_constraint' => false,
			'with_checksum_constraint' => true,
			'with_title_constraint' => false,
		]);
		$this->assertOk($response);
		// We have 3 checksum duplicates in total: 1,2,3
		$response->assertSee($photo1->id);
		$response->assertSee($photo2->id);
		$response->assertSee($photo3->id);
		$response->assertDontSee($photo4->id);

		// We cannot test further with requests due to the SE limitations.
		// So we directly interact with Action class.

		$duplicate_finder = new DuplicateFinder();

		/** @var Collection<int,object{album_id:string,album_title:string,photo_id:string,photo_title:string,checksum:string,short_path:string|null,storage_disk:string|null}> */
		$collection = $duplicate_finder->search(
			must_be_within_same_album: true,
			must_have_same_checksum: true,
			must_have_same_title: false);

		// We have 2 checksum duplicates in the same album: 1,2
		self::assertTrue($collection->contains('photo_id', $photo1->id));
		self::assertTrue($collection->contains('photo_id', $photo2->id));
		self::assertFalse($collection->contains('photo_id', $photo3->id));
		self::assertFalse($collection->contains('photo_id', $photo4->id));

		$collection = $duplicate_finder->search(
			must_be_within_same_album: true,
			must_have_same_checksum: true,
			must_have_same_title: true);

		// We have 0 checksum duplicates in the same album with the same title
		self::assertFalse($collection->contains('photo_id', $photo1->id));
		self::assertFalse($collection->contains('photo_id', $photo2->id));
		self::assertFalse($collection->contains('photo_id', $photo3->id));
		self::assertFalse($collection->contains('photo_id', $photo4->id));

		$collection = $duplicate_finder->search(
			must_be_within_same_album: true,
			must_have_same_checksum: false,
			must_have_same_title: true);

		// We have 2 title duplicates in the same album: 2,4
		self::assertFalse($collection->contains('photo_id', $photo1->id));
		self::assertTrue($collection->contains('photo_id', $photo2->id));
		self::assertFalse($collection->contains('photo_id', $photo3->id));
		self::assertTrue($collection->contains('photo_id', $photo4->id));

		// Create photo 5: same as photo 1.
		$photo5 = Photo::factory()->owned_by($this->admin)->with_checksum('1234567890abcdef')->with_title('duplicate')->in($album)->create();
		$collection = $duplicate_finder->search(
			must_be_within_same_album: true,
			must_have_same_checksum: true,
			must_have_same_title: true);

		// We have 1 checksum duplicates in the same album with the same title: 1,5
		self::assertTrue($collection->contains('photo_id', $photo1->id));
		self::assertTrue($collection->contains('photo_id', $photo5->id));
		self::assertFalse($collection->contains('photo_id', $photo2->id));
		self::assertFalse($collection->contains('photo_id', $photo3->id));
		self::assertFalse($collection->contains('photo_id', $photo4->id));
	}
}