<?php

declare(strict_types=1);

namespace App\Http\Requests\Traits;

trait HasUserIDsTrait
{
	/**
	 * @var array<int>
	 */
	protected array $userIDs = [];

	/**
	 * @return array<int>
	 */
	public function userIDs(): array
	{
		return $this->userIDs;
	}
}
