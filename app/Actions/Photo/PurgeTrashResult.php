<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\Photo;

/**
 * Outcome of one purge run (Feature 119, DO-119-04).
 */
final readonly class PurgeTrashResult
{
	/**
	 * @param int  $purged           photos deleted
	 * @param int  $restored         photos taken out of the trash because they had an album link again
	 * @param int  $left             photos still in the trash after the run
	 * @param bool $budget_exhausted the run stopped because its time budget was used
	 */
	public function __construct(
		public int $purged,
		public int $restored,
		public int $left,
		public bool $budget_exhausted,
	) {
	}
}
