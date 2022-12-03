<?php

namespace App\Http\Requests\Contracts;

interface HasSizeVariant
{
	/**
	 * @return string
	 */
	public function sizeVariant(): string;
}