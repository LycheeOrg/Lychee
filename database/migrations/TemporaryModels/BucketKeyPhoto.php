<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Eloquent\Model;

/**
 * Model class for the Feature 092 backfill of `photos.taken_key` and
 * `photos.title_date_key`.
 */
class BucketKeyPhoto extends Model
{
	protected $table = 'photos';

	public $timestamps = false;
}
