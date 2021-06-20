<?php

namespace App\Models\Extensions;

trait PhotoCast
{
	public function toReturnArray(): array
	{
		return $this->toArray();
	}
}
