<?php

namespace App\Http\Requests\Contracts;

interface HasTitle
{
	const TITLE_ATTRIBUTE = 'title';

	/**
	 * @return string|null
	 */
	public function title(): ?string;
}
