<?php

namespace App\Contracts\Relations;

interface BidirectionalRelation
{
	public function getForeignMethodName(): string;
}
