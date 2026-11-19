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

namespace Tests\AssistedVision\NsfwClassification;

use App\Enum\NsfwDetectionLabel;
use App\Enum\NsfwSensitiveAlbumAction;
use App\Enum\NsfwStatus;
use App\Jobs\ApplyNsfwAlbumSensitivityJob;
use App\Models\Configs;
use App\Models\NsfwDetection;
use App\Models\Photo;
use App\Models\PhotoModerationEvent;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v2\Base\BaseApiWithDataTest;
use Tests\Traits\RequireSE;

/**
 * Feature 089 (FR-089-10, FR-089-11, FR-089-14, S-089-18 … S-089-22): NSFW findings deletion.
 */
class NsfwFindingsDeleteTest extends BaseApiWithDataTest
{
	use RequireSE;

	private ?string $saved_album_action = null;

	public function setUp(): void
	{
		parent::setUp();
		$this->requireSe();
		$this->saved_album_action = Configs::query()->where('key', '=', 'ai_vision_nsfw_sensitive_album_action')->value('value');
	}

	public function tearDown(): void
	{
		DB::table('nsfw_detections')->delete();
		Configs::set('ai_vision_nsfw_sensitive_album_action', $this->saved_album_action ?? '');
		$this->resetSe();
		parent::tearDown();
	}

	public function testDeletesFindingsById(): void
	{
		$this->hold($this->photo1);
		$deleted = $this->addDetection($this->photo1, NsfwDetectionLabel::BUTTOCKS_EXPOSED);
		$kept = $this->addDetection($this->photo1, NsfwDetectionLabel::FEET_EXPOSED);

		$response = $this->actingAs($this->admin)->deleteJson('NsfwDetection/findings', ['detection_ids' => [$deleted->id]]);

		$this->assertNoContent($response);
		self::assertNull(NsfwDetection::query()->find($deleted->id));
		self::assertNotNull(NsfwDetection::query()->find($kept->id));
		$this->assertStillHeld($this->photo1);
	}

	public function testDeletesFindingsByPhoto(): void
	{
		$this->hold($this->photo1);
		$this->addDetection($this->photo1, NsfwDetectionLabel::BUTTOCKS_EXPOSED);
		$this->addDetection($this->photo1, NsfwDetectionLabel::FEET_EXPOSED);
		$kept = $this->addDetection($this->photo2, NsfwDetectionLabel::FEET_EXPOSED);

		$response = $this->actingAs($this->admin)->deleteJson('NsfwDetection/findings/photos', ['photo_ids' => [$this->photo1->id]]);

		$this->assertNoContent($response);
		self::assertSame(0, NsfwDetection::query()->where('photo_id', '=', $this->photo1->id)->count());
		self::assertNotNull(NsfwDetection::query()->find($kept->id));
		$this->assertStillHeld($this->photo1);

		$listing = $this->actingAs($this->admin)->getJsonWithData('NsfwDetection/findings', ['page' => 1, 'per_page' => 30]);
		$listing->assertJsonPath('total', 1);
		$listing->assertJsonPath('photos.0.photo_id', $this->photo2->id);
	}

	public function testValidatesBodies(): void
	{
		$this->actingAs($this->admin);
		$this->assertUnprocessable($this->deleteJson('NsfwDetection/findings', []));
		$this->assertUnprocessable($this->deleteJson('NsfwDetection/findings', ['detection_ids' => []]));
		$this->assertUnprocessable($this->deleteJson('NsfwDetection/findings', ['detection_ids' => range(1, 501)]));
		$this->assertUnprocessable($this->deleteJson('NsfwDetection/findings', ['detection_ids' => ['abc']]));
		$this->assertUnprocessable($this->deleteJson('NsfwDetection/findings', ['detection_ids' => [0]]));
		$this->assertUnprocessable($this->deleteJson('NsfwDetection/findings/photos', []));
		$this->assertUnprocessable($this->deleteJson('NsfwDetection/findings/photos', ['photo_ids' => []]));
		$this->assertUnprocessable($this->deleteJson('NsfwDetection/findings/photos', ['photo_ids' => ['not-a-random-id']]));
		$this->assertUnprocessable($this->deleteJson('NsfwDetection/findings/photos', ['photo_ids' => array_fill(0, 501, $this->photo1->id)]));
	}

	public function testAccessControl(): void
	{
		$detection = $this->addDetection($this->photo1, NsfwDetectionLabel::FEET_EXPOSED);
		$by_id = ['detection_ids' => [$detection->id]];
		$by_photo = ['photo_ids' => [$this->photo1->id]];

		$this->assertUnauthorized($this->deleteJson('NsfwDetection/findings', $by_id));
		$this->assertUnauthorized($this->deleteJson('NsfwDetection/findings/photos', $by_photo));
		$this->assertForbidden($this->actingAs($this->userMayUpload1)->deleteJson('NsfwDetection/findings', $by_id));
		$this->assertForbidden($this->actingAs($this->userMayUpload1)->deleteJson('NsfwDetection/findings/photos', $by_photo));

		$this->resetSe();
		$this->assertSupporterRequired($this->actingAs($this->admin)->deleteJson('NsfwDetection/findings', $by_id));
		$this->assertSupporterRequired($this->actingAs($this->admin)->deleteJson('NsfwDetection/findings/photos', $by_photo));

		self::assertNotNull(NsfwDetection::query()->find($detection->id));
	}

	public function testApprovalAfterDismissingSensitiveFindingDoesNotMarkAlbums(): void
	{
		Bus::fake([ApplyNsfwAlbumSensitivityJob::class]);
		Configs::set('ai_vision_nsfw_sensitive_album_action', NsfwSensitiveAlbumAction::MARK_ALBUM->value);
		$this->hold($this->photo1);
		$this->addDetection($this->photo1, NsfwDetectionLabel::FEET_EXPOSED, is_sensitive: true);

		$this->assertNoContent($this->actingAs($this->admin)->deleteJson('NsfwDetection/findings/photos', ['photo_ids' => [$this->photo1->id]]));
		$this->assertNoContent($this->actingAs($this->admin)->postJson('Moderation::approve', ['photo_ids' => [$this->photo1->id]]));

		Bus::assertNotDispatched(ApplyNsfwAlbumSensitivityJob::class);
		$this->photo1->refresh();
		self::assertTrue($this->photo1->is_validated);
		self::assertSame(NsfwStatus::VISIBLE, $this->photo1->nsfw_status);
	}

	private function hold(Photo $photo): void
	{
		Photo::query()->where('id', '=', $photo->id)->update(['nsfw_status' => NsfwStatus::REVIEW->value, 'is_validated' => false]);
	}

	private function assertStillHeld(Photo $photo): void
	{
		$photo->refresh();
		self::assertSame(NsfwStatus::REVIEW, $photo->nsfw_status);
		self::assertFalse($photo->is_validated);
		self::assertSame(0, PhotoModerationEvent::query()->where('photo_id', '=', $photo->id)->count());
	}

	private function addDetection(Photo $photo, NsfwDetectionLabel $label, bool $is_sensitive = false): NsfwDetection
	{
		return NsfwDetection::query()->create([
			'photo_id' => $photo->id,
			'label' => $label,
			'confidence' => 0.7,
			'bbox_x' => 1,
			'bbox_y' => 2,
			'bbox_width' => 3,
			'bbox_height' => 4,
			'is_block' => false,
			'is_review' => !$is_sensitive,
			'is_sensitive' => $is_sensitive,
		]);
	}
}
