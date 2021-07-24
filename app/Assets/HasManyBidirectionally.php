<?php

namespace App\Assets;

use App\Contracts\BidirectionalRelation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HasManyBidirectionally extends HasMany implements BidirectionalRelation
{
	use BidirectionalRelationTrait;

	public function __construct(Builder $query, Model $parent, string $foreignKey, string $localKey, string $foreignMethodName)
	{
		parent::__construct($query, $parent, $foreignKey, $localKey);
		$this->foreignMethodName = $foreignMethodName;
	}
}