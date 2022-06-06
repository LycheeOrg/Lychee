<?php

namespace App\Models\Extensions;

use App\Database\Query\NullBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentQueryBuilder;
use Illuminate\Database\Query\Builder as BaseQueryBuilder;

class NullModelBuilder extends FixedQueryBuilder
{
	public function __construct(BaseQueryBuilder $query)
	{
		parent::__construct(NullBuilder::createFromQueryBuilder($query));
	}

	public static function createFromQueryBuilder(EloquentQueryBuilder $query): self
	{
		$instance = new self($query->getQuery());
		$instance->setModel($query->getModel());

		return $instance;
	}
}
