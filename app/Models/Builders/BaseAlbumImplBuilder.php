<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Models\Builders;

use App\Eloquent\FixedQueryBuilder;
use App\Models\BaseAlbumImpl;

/**
 * Specialized query builder for {@link \App\Models\BaseAlbumImpl}.
 * @template TModelClass of BaseAlbumImpl
 * @extends FixedQueryBuilder<TModelClass>
 */
class BaseAlbumImplBuilder extends FixedQueryBuilder
{
}