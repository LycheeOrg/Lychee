<?php

namespace App\Http\Requests\Contracts;

interface HasTitle
{
	public const TITLE_ATTRIBUTE = 'title';

	/**
	 * @return string|null
	 */
	public function title(): ?string;
}
