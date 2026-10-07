<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO;

/**
 * Whether a newer Lychee is available, from both sources:
 * - a new release: version.md is older than the remote update.json,
 * - a git update: the local master HEAD is behind GitHub master.
 */
final readonly class UpdateAvailability
{
	public function __construct(
		public bool $enabled,
		public bool $is_new_release_available,
		public bool $is_git_update_available,
		public ?int $commits_behind,
		public ?string $current_version,
		public ?string $latest_version,
	) {
	}

	/**
	 * Update checks are turned off: nothing was checked.
	 */
	public static function disabled(): self
	{
		return new self(
			enabled: false,
			is_new_release_available: false,
			is_git_update_available: false,
			commits_behind: null,
			current_version: null,
			latest_version: null,
		);
	}
}
