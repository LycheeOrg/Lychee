<?php

declare(strict_types=1);

namespace App\Contracts\Relations;

interface BidirectionalRelation
{
	public function getForeignMethodName(): string;
}
