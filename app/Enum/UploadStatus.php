<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Enum;

/**
 * Outcome of a single-file upload, read back by its provisional ID (Feature 116).
 */
enum UploadStatus: string
{
	/** Last chunk received; the job is queued, running, or released for a retry. */
	case PROCESSING = 'processing';
	/** A photo was created (or a live-photo video joined its still) in the target album. */
	case COMPLETED = 'completed';
	/** The same file already existed; that photo is now in the target album. */
	case DUPLICATE = 'duplicate';
	/** Not added to the target album (duplicate skipped, resynced, waiting for moderation, or rejected). */
	case SKIPPED = 'skipped';
	/** Processing failed after its last attempt. */
	case FAILED = 'failed';
	/** No readable entry: expired, never existed, or not the caller's. */
	case UNKNOWN = 'unknown';
}
