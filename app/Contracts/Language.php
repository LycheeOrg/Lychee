<?php

namespace App\Contracts;

interface Language
{
	public function code(): string;

	/**
	 * @return array<string, string>
	 */
	public function get_locale(): array;
}
