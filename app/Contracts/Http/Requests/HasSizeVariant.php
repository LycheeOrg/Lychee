<?php

namespace App\Contracts\Http\Requests;

interface HasSizeVariant
{
	/**
	 * @return string
	 */
	public function sizeVariant(): string;
}