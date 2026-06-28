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
 * - CHECK:            Uploads are hidden until an admin approves them.
 * - MONITOR:          Uploads are immediately visible; NSFW scanning always applied.
 * - TRUST_BUT_VERIFY: Uploads are immediately visible; NSFW scanning always applied;
 *                     review findings auto-approved, block findings configurable.
 * - TRUSTED:          Uploads are immediately visible (subject to album visibility).
 */
enum UserUploadTrustLevel: string
{
	case CHECK = 'check';
	case MONITOR = 'monitor';
	case TRUST_BUT_VERIFY = 'trust_but_verify';
	case TRUSTED = 'trusted';
}
