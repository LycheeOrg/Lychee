<?php

namespace App\Contracts\Http\Requests;

interface HasOwnerId
{
	/**
	 * @return int|null
	 */
	public function ownerId(): ?int;
}
