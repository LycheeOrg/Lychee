<?php

namespace App\Http\Requests\Traits;

trait HasIsPublicTrait
{
	protected bool $is_public = false;

	/**
	 * @return bool
	 */
	public function is_public(): bool
	{
		return $this->is_public;
	}
}
