<?php

declare(strict_types=1);

namespace App\Contracts\Http\Requests;

interface HasUsername
{
	/**
	 * @return string
	 */
	public function username(): string;
}
