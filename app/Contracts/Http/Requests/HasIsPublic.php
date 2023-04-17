<?php

namespace App\Contracts\Http\Requests;

interface HasIsPublic
{
	/**
	 * @return bool
	 */
	public function is_public(): bool;
}