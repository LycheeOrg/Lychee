<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models\Builders;

use App\Eloquent\FixedQueryBuilder;

/**
 * Specialized query builder for {@link \App\Models\Photo}.
 *
 * @template TModelClass of \App\Models\Photo
 *
 * @extends FixedQueryBuilder<TModelClass>
 */
class PhotoBuilder extends FixedQueryBuilder
{
}