<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

/**
 * @noinspection PhpDocMissingThrowsInspection
 * @noinspection PhpUnhandledExceptionInspection
 */

namespace Tests\AssistedVision\Face;

use App\Models\Configs;
use App\Models\Face;
use App\Models\Person;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class PhotoFacesTest extends BaseApiWithDataTest
{
	private Person $searchable_person;
	private Person $hidden_person;

	public function setUp(): void
	{
		parent::setUp();

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
		Configs::set('ai_vision_face_permission_mode', 'restricted');
		Configs::set('ai_vision_face_person_is_searchable_default', '1');

		$this->searchable_person = Person::factory()->create([
			'is_searchable' => true,
		]);
		$this->hidden_person = Person::factory()->create([
			'is_searchable' => false,
			'user_id' => $this->userMayUpload2->id,
		]);
	}

	public function tearDown(): void
	{
		DB::table('face_suggestions')->delete();
		DB::table('faces')->delete();
		DB::table('persons')->delete();
		parent::tearDown();
	}

	public function testOwnerGetsPhotoFacesPayload(): void
	{
		Face::factory()->for_photo($this->photo1)->for_person($this->searchable_person)->create();
		Face::factory()->for_photo($this->photo1)->for_person($this->hidden_person)->create();
		/** @var Authenticatable $owner */
		$owner = $this->userMayUpload1;

		$response = $this->actingAs($owner)->getJson('Photo/' . $this->photo1->id . '/faces');

		$this->assertOk($response);
		self::assertCount(1, $response->json('faces'));
		self::assertSame(1, $response->json('hidden_face_count'));
		self::assertIsBool($response->json('rights.can_view_face_overlays'));
		self::assertIsBool($response->json('rights.can_dismiss_face'));
		self::assertIsBool($response->json('rights.can_assign_face'));
		self::assertIsBool($response->json('rights.can_trigger_scan'));
	}

	public function testNonOwnerGetsForbidden(): void
	{
		Face::factory()->for_photo($this->photo1)->for_person($this->searchable_person)->create();
		/** @var Authenticatable $non_owner */
		$non_owner = $this->userNoUpload;

		$response = $this->actingAs($non_owner)->getJson('Photo/' . $this->photo1->id . '/faces');

		$this->assertForbidden($response);
	}
}
