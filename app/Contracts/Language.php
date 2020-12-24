<?php

namespace App\Contracts;

interface Language
{
	public function code();

	public function get_locale();
}
