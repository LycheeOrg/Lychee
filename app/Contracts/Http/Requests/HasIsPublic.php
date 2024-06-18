<?php

declare(strict_types=1);

namespace App\Contracts\Http\Requests;

interface HasIsPublic
{
	/**
	 * @return bool
	 */
	public function is_public(): bool;
}