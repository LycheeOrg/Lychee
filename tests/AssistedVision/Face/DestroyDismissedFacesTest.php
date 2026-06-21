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

use App\Jobs\DeleteFaceEmbeddingsJob;
use App\Models\Configs;
use App\Models\Face;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\Feature_v2\Base\BaseApiWithDataTest;

class DestroyDismissedFacesTest extends BaseApiWithDataTest
{
	public function setUp(): void
	{
		parent::setUp();

		Configs::set('ai_vision_enabled', '1');
		Configs::set('ai_vision_face_enabled', '1');
	}

	public function tearDown(): void
	{
		DB::table('face_suggestions')->delete();
		DB::table('faces')->delete();
		DB::table('persons')->delete();
		parent::tearDown();
	}

	// ── CHECK (GET) ─────────────────────────────────────────────

	public function testCheckAsGuest(): void
	{
		$response = $this->getJson('Maintenance::destroyDismissedFaces');
		$this->assertUnauthorized($response);
	}

	public function testCheckAsUser(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->getJson('Maintenance::destroyDismissedFaces');
		$this->assertForbidden($response);
	}

	public function testCheckReturnsZeroWhenAiVisionDisabled(): void
	{
		Configs::set('ai_vision_enabled', '0');

		$response = $this->actingAs($this->admin)->getJson('Maintenance::destroyDismissedFaces');
		$this->assertOk($response);
		self::assertEquals(0, $response->json());
	}

	public function testCheckReturnsCountOfDismissedFaces(): void
	{
		Face::factory()->for_photo($this->photo1)->dismissed()->count(3)->create();
		Face::factory()->for_photo($this->photo1)->count(2)->create();

		$response = $this->actingAs($this->admin)->getJson('Maintenance::destroyDismissedFaces');
		$this->assertOk($response);
		self::assertEquals(3, $response->json());
	}

	public function testCheckReturnsZeroWhenNoDismissedFaces(): void
	{
		Face::factory()->for_photo($this->photo1)->count(2)->create();

		$response = $this->actingAs($this->admin)->getJson('Maintenance::destroyDismissedFaces');
		$this->assertOk($response);
		self::assertEquals(0, $response->json());
	}

	// ── DO (POST) ───────────────────────────────────────────────

	public function testDoAsGuest(): void
	{
		$response = $this->postJson('Maintenance::destroyDismissedFaces');
		$this->assertUnauthorized($response);
	}

	public function testDoAsUser(): void
	{
		$response = $this->actingAs($this->userMayUpload1)->postJson('Maintenance::destroyDismissedFaces');
		$this->assertForbidden($response);
	}

	public function testDoDeletesDismissedFaces(): void
	{
		Bus::fake([DeleteFaceEmbeddingsJob::class]);
		Storage::fake('images');

		$dismissed = Face::factory()->for_photo($this->photo1)->dismissed()->count(2)->create();
		$kept = Face::factory()->for_photo($this->photo1)->create();

		$response = $this->actingAs($this->admin)->postJson('Maintenance::destroyDismissedFaces');
		$this->assertOk($response);

		$json = $response->json();
		self::assertEquals(2, $json['deleted_count']);

		foreach ($dismissed as $face) {
			self::assertNull(Face::find($face->id));
		}
		self::assertNotNull(Face::find($kept->id));

		Bus::assertDispatched(DeleteFaceEmbeddingsJob::class);
	}

	public function testDoDeletesCropFiles(): void
	{
		Bus::fake([DeleteFaceEmbeddingsJob::class]);
		Storage::fake('images');

		$crop_token = 'abcdef1234567890testtest';
		$crop_path = 'faces/' . substr($crop_token, 0, 2) . '/' . substr($crop_token, 2, 2) . '/' . $crop_token . '.jpg';
		Storage::disk('images')->put($crop_path, 'fake-image-data');

		Face::factory()->for_photo($this->photo1)->dismissed()->create(['crop_token' => $crop_token]);

		$response = $this->actingAs($this->admin)->postJson('Maintenance::destroyDismissedFaces');
		$this->assertOk($response);

		self::assertEquals(1, $response->json('deleted_count'));
		Storage::disk('images')->assertMissing($crop_path);
	}

	public function testDoHandlesNullCropToken(): void
	{
		Bus::fake([DeleteFaceEmbeddingsJob::class]);

		Face::factory()->for_photo($this->photo1)->dismissed()->without_crop()->create();

		$response = $this->actingAs($this->admin)->postJson('Maintenance::destroyDismissedFaces');
		$this->assertOk($response);

		self::assertEquals(1, $response->json('deleted_count'));
	}

	public function testDoReturnsZeroWhenNoDismissedFaces(): void
	{
		Bus::fake([DeleteFaceEmbeddingsJob::class]);

		Face::factory()->for_photo($this->photo1)->count(2)->create();

		$response = $this->actingAs($this->admin)->postJson('Maintenance::destroyDismissedFaces');
		$this->assertOk($response);

		self::assertEquals(0, $response->json('deleted_count'));

		Bus::assertNotDispatched(DeleteFaceEmbeddingsJob::class);
	}
}
