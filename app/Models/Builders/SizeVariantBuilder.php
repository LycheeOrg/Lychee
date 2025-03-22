<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models\Builders;

use App\Eloquent\FixedQueryBuilder;
use App\Models\SizeVariant;

/**
 * Specialized query builder for {@link \App\Models\SizeVariant}.
 *
 * @template TModelClass of SizeVariant
 * @extends FixedQueryBuilder<TModelClass>
 */
class SizeVariantBuilder extends FixedQueryBuilder
{
}