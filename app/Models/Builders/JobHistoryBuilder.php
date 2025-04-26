<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models\Builders;

use App\Eloquent\FixedQueryBuilder;
use App\Models\JobHistory;

/**
 * Specialized query builder for {@link \App\Models\JobHistory}.
 *
 * @template TModelClass of JobHistory
 *
 * @extends FixedQueryBuilder<TModelClass>
 */
class JobHistoryBuilder extends FixedQueryBuilder
{
}