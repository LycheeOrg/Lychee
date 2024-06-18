<?php

declare(strict_types=1);

namespace App\Contracts\Http\Requests;

interface HasTitle
{
	/**
	 * @return string|null
	 */
	public function title(): ?string;
}
