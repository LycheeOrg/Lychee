<?php

namespace App\Legacy\V1\Requests\Traits;

trait HasUserIDTrait
{
	/**
	 * @var int
	 */
	protected int $userID;

	/**
	 * @return int
	 */
	public function userID(): int
	{
		return $this->userID;
	}
}
