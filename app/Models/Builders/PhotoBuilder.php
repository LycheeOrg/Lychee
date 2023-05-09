<?php

namespace App\Models\Builders;

use App\Eloquent\FixedQueryBuilder;
use Illuminate\Database\Eloquent\Builder;

/**
 * Specialized query builder for {@link \App\Models\Photo}.
 *
 * @extends FixedQueryBuilder<\App\Models\Photo>
 */
class PhotoBuilder extends FixedQueryBuilder
{
}