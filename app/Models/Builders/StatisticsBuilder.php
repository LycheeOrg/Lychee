<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models\Builders;

use App\Eloquent\FixedQueryBuilder;
use App\Models\Statistics;

/**
 * Specialized query builder for {@link \App\Models\Statistics}.
 *
 * @template TModelClass of Statistics
 *
 * @extends FixedQueryBuilder<TModelClass>
 */
class StatisticsBuilder extends FixedQueryBuilder
{
}