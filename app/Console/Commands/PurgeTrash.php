<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Console\Commands;

use App\Actions\Photo\PurgeTrash as PurgeTrashAction;
use Illuminate\Console\Command;

/**
 * Feature 119 (CLI-119-01): purges the trash until it is empty.
 *
 * For installs without a queue worker: deleted photos whose purge could not
 * finish after the response are removed here (for example from cron).
 */
class PurgeTrash extends Command
{
	protected $signature = 'lychee:purge-trash';

	protected $description = 'Deletes the photos waiting in the trash (rows and files)';

	public function handle(PurgeTrashAction $purge): int
	{
		$result = $purge->run();
		$this->line(sprintf('Purged %d photos, %d restored, %d left.', $result->purged, $result->restored, $result->left));

		return $result->left === 0 ? self::SUCCESS : self::FAILURE;
	}
}
