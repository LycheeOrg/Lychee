<?php

namespace App\Http\Requests\Traits;

trait HasCompactBooleanTrait
{
	protected bool $is_compact;

	public function is_compact(): bool
	{
		return $this->is_compact;
	}
}
