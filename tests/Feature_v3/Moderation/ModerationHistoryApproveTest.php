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
use App\Models\Configs;
use App\Models\Photo;
use App\Models\PhotoModerationEvent;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Tests\Feature_v3\Base\BaseApiWithDataTest;

/**
 * Feature 089 (FR-089-03, NFR-089-03, S-089-09 … S-089-11): approvals recorded by Moderation::approve.
 */
class ModerationHistoryApproveTest extends BaseApiWithDataTest
{
	private const CONFIGS = [
		'moderation_history_log_system',
		'moderation_history_log_approvals',
	];

	/** @var array<string,string|null> */
	private array $saved_configs = [];

	public function setUp(): void
	{
		parent::setUp();
		foreach (self::CONFIGS as $key) {
			$this->saved_configs[$key] = Configs::query()->where('key', '=', $key)->value('value');
		}
	}

	public function tearDown(): void
	{
		foreach ($this->saved_configs as $key => $value) {
			Configs::set($key, $value ?? '');
		}
		parent::tearDown();
	}

	public function testApprovalRecordsHiddenPhotosOnlyWithOneInsert(): void
	{
		$this->hide($this->photo1, $this->photo2);

		$inserts = $this->countEventInserts(function (): void {
			$response = $this->actingAs($this->admin)->postJson('Moderation::approve', [
				'photo_ids' => [$this->photo1->id, $this->photo2->id, $this->photo3->id],
			]);
			$this->assertNoContent($response);
		});

		self::assertSame(1, $inserts);
		foreach ([$this->photo1, $this->photo2] as $photo) {
			$row = PhotoModerationEvent::query()->where('photo_id', '=', $photo->id)->sole();
			self::assertSame(ModerationEvent::APPROVED, $row->event);
			self::assertSame(ModerationSource::ADMIN, $row->source);
			self::assertSame($this->admin->id, $row->user_id);
			self::assertTrue($photo->refresh()->is_validated);
		}
		self::assertSame(0, PhotoModerationEvent::query()->where('photo_id', '=', $this->photo3->id)->count());
	}

	public function testApprovalOfVisiblePhotosWritesNothing(): void
	{
		$inserts = $this->countEventInserts(function (): void {
			$response = $this->actingAs($this->admin)->postJson('Moderation::approve', [
				'photo_ids' => [$this->photo1->id],
			]);
			$this->assertNoContent($response);
		});

		self::assertSame(0, $inserts);
		self::assertSame(0, PhotoModerationEvent::query()->count());
	}

	public function testApprovalIsRecordedWhenSystemLoggingIsOff(): void
	{
		Configs::set('moderation_history_log_system', '0');
		$this->hide($this->photo1);

		$response = $this->actingAs($this->admin)->postJson('Moderation::approve', [
			'photo_ids' => [$this->photo1->id],
		]);
		$this->assertNoContent($response);

		self::assertSame(1, PhotoModerationEvent::query()->where('photo_id', '=', $this->photo1->id)->count());
	}

	public function testApprovalIsNotRecordedWhenApprovalLoggingIsOff(): void
	{
		Configs::set('moderation_history_log_approvals', '0');
		$this->hide($this->photo1);

		$inserts = $this->countEventInserts(function (): void {
			$response = $this->actingAs($this->admin)->postJson('Moderation::approve', [
				'photo_ids' => [$this->photo1->id],
			]);
			$this->assertNoContent($response);
		});

		self::assertSame(0, $inserts);
		self::assertTrue($this->photo1->refresh()->is_validated);
	}

	private function hide(Photo ...$photos): void
	{
		Photo::query()->whereIn('id', array_map(fn (Photo $p) => $p->id, $photos))->update(['is_validated' => false]);
	}

	/**
	 * Run the callback and count the inserts into photo_moderation_events.
	 */
	private function countEventInserts(\Closure $callback): int
	{
		$count = 0;
		DB::listen(function (QueryExecuted $query) use (&$count): void {
			if (str_starts_with(strtolower(ltrim($query->sql)), 'insert') && str_contains($query->sql, 'photo_moderation_events')) {
				$count++;
			}
		});
		$callback();

		return $count;
	}
}
