<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum AlbumListingScope.
 *
 * The `scope` request dimension shared by root (index/buckets/rights),
 * `/Albums/persons`, and `/Albums/pinned`: required for an authenticated caller,
 * optional-defaulting-to-`SHARED` for a guest — a guest can never request `OWN`
 * (there is no unpartitioned third mode).
 */
enum AlbumListingScope: string
{
	case OWN = 'own';
	case SHARED = 'shared';
}
