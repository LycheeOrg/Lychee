<?php

declare(strict_types=1);

namespace App\Http\Requests\Traits;

trait HasUsernameTrait
{
	protected string $username;

	/**
	 * @return string
	 */
	public function username(): string
	{
		return $this->username;
	}
}
