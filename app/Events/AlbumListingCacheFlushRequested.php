<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Requests one coarse, instance-wide flush of every album-listing cache
 * entry (all six cached query types carry the `album-listing-global` tag).
 *
 * Reserved for genuinely rare/subtree-wide or admin-level operations
 * (settings changes, share-propagate, subtree move/merge/transfer, tree
 * repair) — never for a routine single-album edit.
 */
class AlbumListingCacheFlushRequested
{
	use Dispatchable;
}
