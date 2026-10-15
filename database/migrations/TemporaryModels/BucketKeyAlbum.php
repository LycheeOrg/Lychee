<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Eloquent\Model;

/**
 * Model class for the Feature 092 backfill of `albums.bucket_key`.
 */
class BucketKeyAlbum extends Model
{
	protected $table = 'albums';

	public $timestamps = false;
}
