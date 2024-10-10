<?php

namespace App\Http\Requests\Traits;

trait HasUserIdTrait
{
	/**
	 * @var int
	 */
	protected int $userId;

	/**
	 * @return int
	 */
	public function userId(): int
	{
		return $this->userId;
	}
}
