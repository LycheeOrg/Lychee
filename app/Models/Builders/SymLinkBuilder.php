<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models\Builders;

use App\Eloquent\FixedQueryBuilder;
use App\Models\SymLink;
use Illuminate\Database\Eloquent\Model;

/**
 * Specialized query builder for {@link \App\Models\SymLink}.
 * @template TModelClass of SymLink
 * @extends FixedQueryBuilder<TModelClass>
 */
class SymLinkBuilder extends FixedQueryBuilder
{
}