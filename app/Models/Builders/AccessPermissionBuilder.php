<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models\Builders;

use App\Eloquent\FixedQueryBuilder;
use App\Models\AccessPermission;

/**
 * Specialized query builder for {@link \App\Models\AccessPermission}.
 *
 * @template TModelClass of AccessPermission
 * @extends FixedQueryBuilder<TModelClass>
 */
class AccessPermissionBuilder extends FixedQueryBuilder
{
}