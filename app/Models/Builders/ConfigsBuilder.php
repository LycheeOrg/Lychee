<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models\Builders;

use App\Eloquent\FixedQueryBuilder;
use App\Models\Configs;

/**
 * Specialized query builder for {@link \App\Models\Configs}.
 *
 * @template TModelClass of Configs
 *
 * @extends FixedQueryBuilder<TModelClass>
 */
class ConfigsBuilder extends FixedQueryBuilder
{
}