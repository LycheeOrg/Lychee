<?php

namespace App\Contracts;

interface BidirectionalRelation
{
	public function getForeignMethodName(): string;
}
