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

namespace Tests\Feature_v2\Flow;

use App\Models\Configs;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class FlowTest extends BaseApiWithDataTest
{
	public function testGetAnonymousRoot(): void
	{
		$response = $this->getJson('Flow');
		$this->assertUnauthorized($response);

		Configs::set('flow_public', true);

		$response = $this->getJson('Flow');
		$this->assertOk($response);
		$response->assertJson([
			'albums' => [
				[
					'id' => $this->album4->id,
					'title' => $this->album4->title,
				],
			],
			'current_page' => 1,
			'from' => 1,
			'last_page' => 1,
			'per_page' => 10,
			'to' => 1,
			'total' => 1,
		]);
		$response->assertDontSee($this->album1->id);
		$response->assertDontSee($this->album2->id);
		$response->assertDontSee($this->album3->id);
		$response->assertDontSee($this->album5->id);
		$response->assertDontSee($this->subAlbum1->id);
		$response->assertDontSee($this->tagAlbum1->id);
		$response->assertDontSee($this->subAlbum2->id);
	}

	public function testGetAnonymousBase(): void
	{
		Configs::set('flow_public', true);
		Configs::set('flow_base', $this->album4->id);

		$response = $this->getJson('Flow');
		$this->assertOk($response);
		$response->assertJson([
			'albums' => [
				[
					'id' => $this->subAlbum4->id,
					'title' => $this->subAlbum4->title,
				],
			],
			'current_page' => 1,
			'from' => 1,
			'last_page' => 1,
			'per_page' => 10,
			'to' => 1,
			'total' => 1,
		]);
		$response->assertDontSee($this->album1->id);
		$response->assertDontSee($this->album2->id);
		$response->assertDontSee($this->album3->id);
		$response->assertDontSee($this->album5->id);
		$response->assertDontSee($this->subAlbum1->id);
		$response->assertDontSee($this->tagAlbum1->id);
		$response->assertDontSee($this->subAlbum2->id);

		Configs::set('flow_public', false);
		Configs::set('flow_base', '');
	}

	public function testGetAnonymousWithSubAlbums(): void
	{
		Configs::set('flow_public', true);
		Configs::set('flow_include_sub_albums', true);

		$response = $this->getJson('Flow');
		$this->assertOk($response);
		$response->assertJson([
			'albums' => [],
			'current_page' => 1,
			'from' => 1,
			'last_page' => 1,
			'per_page' => 10,
			'to' => 2,
			'total' => 2,
		]);
		$response->assertSee($this->subAlbum4->id);
		$response->assertSee($this->album4->id);

		$response->assertDontSee($this->album1->id);
		$response->assertDontSee($this->album2->id);
		$response->assertDontSee($this->album3->id);
		$response->assertDontSee($this->album5->id);
		$response->assertDontSee($this->subAlbum1->id);
		$response->assertDontSee($this->tagAlbum1->id);
		$response->assertDontSee($this->subAlbum2->id);
	}

	public function testGetUserRoot(): void
	{
		Configs::set('flow_include_sub_albums', false);

		$response = $this->actingAs($this->userMayUpload1)->getJson('Flow');
		$this->assertOk($response);
		$response->assertJson([
			'albums' => [],
			'current_page' => 1,
			'from' => 1,
			'last_page' => 1,
			'per_page' => 10,
			'to' => 2,
			'total' => 2,
		]);
		$response->assertSee($this->album1->id);
		$response->assertSee($this->album4->id);
	}

	public function testGetUserRootWithSubAlbums(): void
	{
		Configs::set('flow_include_sub_albums', true);
		Configs::set('hide_nsfw_in_flow', false);

		$response = $this->actingAs($this->userMayUpload1)->getJson('Flow');
		$this->assertOk($response);
		$response->assertJson([
			'albums' => [],
			'current_page' => 1,
			'from' => 1,
			'last_page' => 1,
			'per_page' => 10,
			'to' => 4,
			'total' => 4,
		]);

		$response->assertSee($this->subAlbum4->id);
		$response->assertSee($this->album4->id);
		$response->assertSee($this->subAlbum4->id);
		$response->assertSee($this->album4->id);

		Configs::set('hide_nsfw_in_flow', true);
	}
}