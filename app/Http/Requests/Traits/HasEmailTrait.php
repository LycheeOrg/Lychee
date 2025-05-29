<?php

namespace App\Http\Requests\Traits;

trait HasEmailTrait
{
	/**
	 * The email address.
	 */
	protected string $email;

	/**
	 * Get the email address.
	 */
	public function email(): string
	{
		return $this->email;
	}
}
