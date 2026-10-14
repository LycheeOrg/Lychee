<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services;

use App\Enum\ModerationEvent;
use App\Enum\ModerationSource;
use App\Repositories\ConfigManager;
use Illuminate\Support\Facades\DB;

/**
 * Writes the moderation history of photos (Feature 089).
 *
 * Every site that hides or shows a photo calls {@link ModerationHistory::record()}
 * explicitly once the photo row exists. Three settings decide which events are written.
 */
class ModerationHistory
{
	public function __construct(
		private readonly ConfigManager $config_manager,
	) {
	}

	/**
	 * Whether an event is recorded: every setting that applies to it must be on.
	 *
	 * - `log_system` applies to events made by the system (source upload or nsfw_scan),
	 * - `log_approvals` applies to approved events,
	 * - `log_nsfw_holds` applies to held events whose source is nsfw_scan.
	 */
	public static function shouldRecord(
		ModerationEvent $event,
		ModerationSource $source,
		bool $log_system,
		bool $log_approvals,
		bool $log_nsfw_holds,
	): bool {
		$is_system = $source !== ModerationSource::ADMIN;
		$is_approval = $event === ModerationEvent::APPROVED;
		$is_nsfw_hold = $event === ModerationEvent::HELD && $source === ModerationSource::NSFW_SCAN;

		return ($log_system || !$is_system) &&
			($log_approvals || !$is_approval) &&
			($log_nsfw_holds || !$is_nsfw_hold);
	}

	/**
	 * Record one event per photo with a single insert.
	 *
	 * @param string[] $photo_ids
	 * @param int|null $user_id   the acting admin, null for the system
	 */
	public function record(array $photo_ids, ModerationEvent $event, ModerationSource $source, ?int $user_id = null): void
	{
		if ($photo_ids === []) {
			return;
		}

		$should_record = self::shouldRecord(
			event: $event,
			source: $source,
			log_system: $this->config_manager->getValueAsBool('moderation_history_log_system'),
			log_approvals: $this->config_manager->getValueAsBool('moderation_history_log_approvals'),
			log_nsfw_holds: $this->config_manager->getValueAsBool('moderation_history_log_nsfw_holds'),
		);
		if (!$should_record) {
			return;
		}

		$now = date('Y-m-d H:i:s');
		$rows = array_map(fn (string $photo_id) => [
			'photo_id' => $photo_id,
			'event' => $event->value,
			'source' => $source->value,
			'user_id' => $user_id,
			'created_at' => $now,
		], array_values($photo_ids));

		DB::table('photo_moderation_events')->insert($rows);
	}
}
