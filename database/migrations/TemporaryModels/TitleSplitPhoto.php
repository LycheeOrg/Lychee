<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Eloquent\Model;

/**
 * Model class specific for running the Feature 060 `title_base`/`title_index`
 * backfill migration on `photos`.
 * We cannot use the app's `Photo` model directly, so that this migration can
 * run in sequence without depending on app code.
 */
class TitleSplitPhoto extends Model
{
	protected $table = 'photos';
}
