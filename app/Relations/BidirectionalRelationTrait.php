<?php

declare(strict_types=1);

namespace App\Relations;

trait BidirectionalRelationTrait
{
	protected string $foreignMethodName;

	public function getForeignMethodName(): string
	{
		return $this->foreignMethodName;
	}
}
