<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Enum;

enum FileStatus: string
{
	case UPLOADING = 'uploading';
	case PROCESSING = 'processing';
	case READY = 'ready';
	case SKIPPED = 'skipped';
	case DONE = 'done';
	case ERROR = 'error';
}
