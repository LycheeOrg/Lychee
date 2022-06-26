<?php

namespace App\Console\Commands\Utilities;

class Colorize
{
	public function red(string $string): string
	{
		return '<fg=red>' . $string . '</>';
	}

	public function magenta(string $string): string
	{
		return '<fg=magenta>' . $string . '</>';
	}

	public function green(string $string): string
	{
		return '<fg=green>' . $string . '</>';
	}

	public function yellow(string $string): string
	{
		return '<fg=yellow>' . $string . '</>';
	}

	public function cyan(string $string): string
	{
		return '<fg=cyan>' . $string . '</>';
	}

	public function blue(string $string): string
	{
		return '<fg=blue>' . $string . '</>';
	}
}
