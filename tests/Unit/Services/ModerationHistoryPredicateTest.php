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

namespace Tests\Unit\Services;

use App\Enum\ModerationEvent;
use App\Enum\ModerationSource;
use App\Services\ModerationHistory;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\AbstractTestCase;

/**
 * Unit tests for {@link ModerationHistory::shouldRecord()} (Feature 089, FR-089-04).
 *
 * An event is recorded only when every setting that applies to it is on:
 * - log_system applies to events whose source is upload or nsfw_scan,
 * - log_approvals applies to approved events,
 * - log_nsfw_holds applies to held events whose source is nsfw_scan.
 */
class ModerationHistoryPredicateTest extends AbstractTestCase
{
	private const SYSTEM = 'system';
	private const APPROVALS = 'approvals';
	private const NSFW_HOLDS = 'nsfw_holds';

	/**
	 * Settings that apply to each (event, source) pair.
	 *
	 * @return array<string,array{0:ModerationEvent,1:ModerationSource,2:string[]}>
	 */
	private static function applicable(): array
	{
		return [
			'held by admin' => [ModerationEvent::HELD, ModerationSource::ADMIN, []],
			'approved by admin' => [ModerationEvent::APPROVED, ModerationSource::ADMIN, [self::APPROVALS]],
			'held at upload' => [ModerationEvent::HELD, ModerationSource::UPLOAD, [self::SYSTEM]],
			'approved at upload' => [ModerationEvent::APPROVED, ModerationSource::UPLOAD, [self::SYSTEM, self::APPROVALS]],
			'held by nsfw scan' => [ModerationEvent::HELD, ModerationSource::NSFW_SCAN, [self::SYSTEM, self::NSFW_HOLDS]],
			'approved by nsfw scan' => [ModerationEvent::APPROVED, ModerationSource::NSFW_SCAN, [self::SYSTEM, self::APPROVALS]],
		];
	}

	/**
	 * Every (event, source) pair against every combination of the three settings.
	 *
	 * @return array<string,array{0:ModerationEvent,1:ModerationSource,2:bool,3:bool,4:bool,5:bool}>
	 */
	public static function cases(): array
	{
		$cases = [];
		foreach (self::applicable() as $name => [$event, $source, $settings]) {
			foreach ([false, true] as $log_system) {
				foreach ([false, true] as $log_approvals) {
					foreach ([false, true] as $log_nsfw_holds) {
						$enabled = [
							self::SYSTEM => $log_system,
							self::APPROVALS => $log_approvals,
							self::NSFW_HOLDS => $log_nsfw_holds,
						];
						$expected = collect($settings)->every(fn (string $setting) => $enabled[$setting]);
						$label = sprintf('%s, system=%d approvals=%d nsfw_holds=%d', $name, $log_system, $log_approvals, $log_nsfw_holds);
						$cases[$label] = [$event, $source, $log_system, $log_approvals, $log_nsfw_holds, $expected];
					}
				}
			}
		}

		return $cases;
	}

	#[DataProvider('cases')]
	public function testShouldRecord(
		ModerationEvent $event,
		ModerationSource $source,
		bool $log_system,
		bool $log_approvals,
		bool $log_nsfw_holds,
		bool $expected,
	): void {
		self::assertSame($expected, ModerationHistory::shouldRecord(
			event: $event,
			source: $source,
			log_system: $log_system,
			log_approvals: $log_approvals,
			log_nsfw_holds: $log_nsfw_holds,
		));
	}

	public function testAllSettingsOnRecordsEverything(): void
	{
		foreach (ModerationEvent::cases() as $event) {
			foreach (ModerationSource::cases() as $source) {
				self::assertTrue(ModerationHistory::shouldRecord($event, $source, true, true, true));
			}
		}
	}

	public function testAdminApprovalIgnoresSystemSetting(): void
	{
		self::assertTrue(ModerationHistory::shouldRecord(ModerationEvent::APPROVED, ModerationSource::ADMIN, false, true, false));
	}

	public function testUploadHoldIgnoresNsfwHoldsSetting(): void
	{
		self::assertTrue(ModerationHistory::shouldRecord(ModerationEvent::HELD, ModerationSource::UPLOAD, true, false, false));
	}
}
