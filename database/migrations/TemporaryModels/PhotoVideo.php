<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

use Illuminate\Database\Eloquent\Model;

/**
 * Model class specific for running the migration of rating average.
 * We cannot use the Model directly as we need to make sure that all migrations can be run in sequence
 * without depending on the app code.
 */
class PhotoVideo extends Model
{
	protected $table = 'photos';
}
