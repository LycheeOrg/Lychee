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

namespace Tests\Feature_v3\Moderation;

use App\Enum\ModerationEvent;
use App\Enum\ModerationSource;
use App\Models\Photo;
use App\Models\PhotoModerationEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 089 (FR-089-05, FR-089-06, NFR-089-01, NFR-089-02, S-089-13 … S-089-16):
 * moderation history endpoints.
 */
class ModerationHistoryListTest extends BaseApiWithDataTest
{
	private const PAGING = ['page' => 1, 'per_page' => 30];

	public function testHistoryListsEventsNewestFirst(): void
	{
		$held = $this->addEvent($this->photo1, ModerationEvent::HELD, ModerationSource::UPLOAD);
		$approved = $this->addEvent($this->photo1, ModerationEvent::APPROVED, ModerationSource::ADMIN, $this->admin);

		$response = $this->actingAs($this->admin)->getJsonWithData('Moderation::history', self::PAGING);

		$this->assertOk($response);
		$response->assertJsonPath('total', 2);
		$response->assertJsonPath('current_page', 1);
		$response->assertJsonPath('last_page', 1);
		$response->assertJsonPath('per_page', 30);
		$response->assertJsonPath('events.0.id', $approved->id);
		$response->assertJsonPath('events.1.id', $held->id);
		$response->assertJsonPath('events.0.photo_id', $this->photo1->id);
		$response->assertJsonPath('events.0.photo_title', $this->photo1->title);
		$response->assertJsonPath('events.0.owner_username', $this->photo1->owner->username);
		$response->assertJsonPath('events.0.event', 'approved');
		$response->assertJsonPath('events.0.source', 'admin');
		$response->assertJsonPath('events.0.username', $this->admin->username);
		$response->assertJsonPath('events.1.event', 'held');
		$response->assertJsonPath('events.1.source', 'upload');
		$response->assertJsonPath('events.1.username', null);
		self::assertNotNull($response->json('events.0.created_at'));
		self::assertArrayHasKey('thumb_url', $response->json('events.0'));
	}

	public function testHistoryIsPaginated(): void
	{
		$oldest = $this->addEvent($this->photo1, ModerationEvent::HELD, ModerationSource::UPLOAD);
		$this->addEvent($this->photo2, ModerationEvent::HELD, ModerationSource::UPLOAD);
		$this->addEvent($this->photo3, ModerationEvent::HELD, ModerationSource::UPLOAD);

		$response = $this->actingAs($this->admin)->getJsonWithData('Moderation::history', ['page' => 2, 'per_page' => 2]);

		$this->assertOk($response);
		$response->assertJsonPath('total', 3);
		$response->assertJsonPath('current_page', 2);
		$response->assertJsonPath('last_page', 2);
		$response->assertJsonCount(1, 'events');
		$response->assertJsonPath('events.0.id', $oldest->id);
	}

	public function testHistoryAccessControl(): void
	{
		$this->assertUnauthorized($this->getJsonWithData('Moderation::history', self::PAGING));
		$this->assertForbidden($this->actingAs($this->userMayUpload1)->getJsonWithData('Moderation::history', self::PAGING));
	}

	public function testHistoryValidatesPaging(): void
	{
		$this->assertUnprocessable($this->actingAs($this->admin)->getJsonWithData('Moderation::history', []));
		$this->assertUnprocessable($this->actingAs($this->admin)->getJsonWithData('Moderation::history', ['page' => 0, 'per_page' => 30]));
		$this->assertUnprocessable($this->actingAs($this->admin)->getJsonWithData('Moderation::history', ['page' => 1, 'per_page' => 101]));
	}

	public function testPhotoHistoryListsOnlyThatPhoto(): void
	{
		$held = $this->addEvent($this->photo1, ModerationEvent::HELD, ModerationSource::NSFW_SCAN);
		$this->addEvent($this->photo2, ModerationEvent::HELD, ModerationSource::UPLOAD);
		$approved = $this->addEvent($this->photo1, ModerationEvent::APPROVED, ModerationSource::NSFW_SCAN);

		$response = $this->actingAs($this->admin)->getJsonWithData('Moderation::photoHistory', ['photo_id' => $this->photo1->id]);

		$this->assertOk($response);
		$response->assertJsonCount(2);
		$response->assertJsonPath('0.id', $approved->id);
		$response->assertJsonPath('1.id', $held->id);
		$response->assertJsonPath('1.source', 'nsfw_scan');
	}

	public function testPhotoHistoryIsEmptyForPhotoWithoutEvents(): void
	{
		$response = $this->actingAs($this->admin)->getJsonWithData('Moderation::photoHistory', ['photo_id' => $this->photo1->id]);

		$this->assertOk($response);
		$response->assertExactJson([]);
	}

	public function testPhotoHistoryAccessControlAndUnknownPhoto(): void
	{
		$params = ['photo_id' => $this->photo1->id];
		$this->assertUnauthorized($this->getJsonWithData('Moderation::photoHistory', $params));
		$this->assertForbidden($this->actingAs($this->userMayUpload1)->getJsonWithData('Moderation::photoHistory', $params));
		$this->assertUnprocessable($this->actingAs($this->admin)->getJsonWithData('Moderation::photoHistory', []));
		$this->assertNotFound($this->actingAs($this->admin)->getJsonWithData('Moderation::photoHistory', ['photo_id' => str_repeat('a', 24)]));
	}

	public function testDeletingPhotoDeletesItsEvents(): void
	{
		$this->addEvent($this->photo1, ModerationEvent::HELD, ModerationSource::UPLOAD);
		$kept = $this->addEvent($this->photo2, ModerationEvent::HELD, ModerationSource::UPLOAD);

		$response = $this->actingAs($this->userMayUpload1)->deleteJson('Photo', ['photo_ids' => [$this->photo1->id], 'from_id' => $this->album1->id]);
		$this->assertNoContent($response);

		self::assertSame(0, PhotoModerationEvent::query()->where('photo_id', '=', $this->photo1->id)->count());
		self::assertNotNull(PhotoModerationEvent::query()->find($kept->id));
	}

	public function testDeletingAdminKeepsEventsWithoutUser(): void
	{
		$other_admin = User::factory()->may_administrate()->create();
		$event = $this->addEvent($this->photo1, ModerationEvent::APPROVED, ModerationSource::ADMIN, $other_admin);

		$response = $this->actingAs($this->admin)->deleteJson('UserManagement', ['id' => $other_admin->id]);
		$this->assertNoContent($response);

		self::assertNull($event->refresh()->user_id);
		$response = $this->actingAs($this->admin)->getJsonWithData('Moderation::history', self::PAGING);
		$response->assertJsonPath('events.0.id', $event->id);
		$response->assertJsonPath('events.0.username', null);
	}

	public function testHistoryQueryCountIsConstant(): void
	{
		$this->addEvent($this->photo1, ModerationEvent::APPROVED, ModerationSource::ADMIN, $this->admin);
		// Warm-up: the first request also loads the configs.
		$this->actingAs($this->admin)->getJsonWithData('Moderation::history', self::PAGING);
		$few = $this->countQueries(fn () => $this->actingAs($this->admin)->getJsonWithData('Moderation::history', self::PAGING));

		foreach ([$this->photo2, $this->photo3, $this->photo4, $this->subPhoto1] as $photo) {
			$this->addEvent($photo, ModerationEvent::HELD, ModerationSource::UPLOAD);
			$this->addEvent($photo, ModerationEvent::APPROVED, ModerationSource::ADMIN, $this->admin);
		}
		$many = $this->countQueries(fn () => $this->actingAs($this->admin)->getJsonWithData('Moderation::history', self::PAGING));

		self::assertSame($few, $many);
	}

	private function addEvent(Photo $photo, ModerationEvent $event, ModerationSource $source, ?User $user = null): PhotoModerationEvent
	{
		return PhotoModerationEvent::query()->create([
			'photo_id' => $photo->id,
			'event' => $event,
			'source' => $source,
			'user_id' => $user?->id,
			'created_at' => now(),
		]);
	}

	private function countQueries(\Closure $callback): int
	{
		DB::flushQueryLog();
		DB::enableQueryLog();
		$this->assertOk($callback());
		$count = count(DB::getQueryLog());
		DB::disableQueryLog();

		return $count;
	}
}
