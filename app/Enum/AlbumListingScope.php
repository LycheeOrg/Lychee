<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum AlbumListingScope (Feature 062, DO-062-01).
 *
 * The `scope` request dimension shared by root (index/buckets/rights),
 * `/Albums/persons`, and `/Albums/pinned` (FR-062-02/FR-062-15): required
 * for an authenticated caller, optional-defaulting-to-`SHARED` for a guest —
 * a guest can never request `OWN` (there is no unpartitioned third mode).
 */
enum AlbumListingScope: string
{
	case OWN = 'own';
	case SHARED = 'shared';
}
