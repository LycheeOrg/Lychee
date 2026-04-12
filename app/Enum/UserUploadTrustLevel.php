<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Enum UserUploadTrustLevel.
 *
 * Per-user trust level controlling whether newly uploaded photos are
 * immediately visible to the public or require admin approval first.
 *
 * - CHECK:   Uploads are hidden from the public until an admin approves them.
 * - MONITOR: Reserved for future use; currently behaves identically to TRUSTED.
 * - TRUSTED: Uploads are immediately publicly visible (subject to album visibility).
 */
enum UserUploadTrustLevel: string
{
	case CHECK = 'check';
	case MONITOR = 'monitor';
	case TRUSTED = 'trusted';
}
