<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Governs how a `TITLE`-sorted parent's direct children compute their `bucket_id`.
 * Instance-wide config-backed only (`title_bucket_mode`) — there is no per-album override.
 */
enum TitleBucketMode: string
{
	/** Parse a leading date out of `title` (e.g. "2020-03 something"), same as the existing timeline title-parsing behaviour. */
	case DATE_PREFIX = 'date_prefix';

	/** Bucket = the first `title_bucket_prefix_length` characters of `title_base`. */
	case ALPHABETICAL = 'alphabetical';
}
