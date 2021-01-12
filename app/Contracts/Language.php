<?php

namespace App\Contracts;

interface Language
{
	public function code(): string;

	public function get_locale(): array;
}
