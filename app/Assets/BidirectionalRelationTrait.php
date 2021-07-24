<?php

namespace App\Assets;

trait BidirectionalRelationTrait
{
	protected string $foreignMethodName;

	public function getForeignMethodName(): string
	{
		return $this->foreignMethodName;
	}
}
