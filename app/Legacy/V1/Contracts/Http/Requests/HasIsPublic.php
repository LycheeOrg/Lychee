<?php

namespace App\Legacy\V1\Contracts\Http\Requests;

interface HasIsPublic
{
	/**
	 * @return bool
	 */
	public function is_public(): bool;
}