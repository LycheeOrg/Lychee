<?php

namespace App\Http\Requests\Traits;

trait HasUserIdsTrait
{
	/**
	 * @var array<int>
	 */
	protected array $userIds = [];

	/**
	 * @return array<int>
	 */
	public function userIds(): array
	{
		return $this->userIds;
	}
}
