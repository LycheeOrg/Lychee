<?php

namespace App\Models\Extensions;

use Illuminate\Database\Eloquent\Builder;

/**
 * A query builder which uses the trait {@link FixedQueryBuilderTrait}.
 *
 * This is the "default" query builder for most of our models.
 * This query builder fixes {@link \Illuminate\Database\Eloquent\Builder}
 * such that method used by Lychee throw proper exceptions.
 * See {@link FixedQueryBuilderTrait} for details.
 */
class FixedQueryBuilder extends Builder
{
	use FixedQueryBuilderTrait;
}
