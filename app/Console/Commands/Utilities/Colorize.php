<?php

namespace App\Console\Commands\Utilities;

class Colorize
{
	public function red($string)
	{
		return '<fg=red>' . $string . '</>';
	}

	public function magenta($string)
	{
		return '<fg=magenta>' . $string . '</>';
	}

	public function green($string)
	{
		return '<fg=green>' . $string . '</>';
	}

	public function yellow($string)
	{
		return '<fg=yellow>' . $string . '</>';
	}

	public function cyan($string)
	{
		return '<fg=cyan>' . $string . '</>';
	}

	public function blue($string)
	{
		return '<fg=blue>' . $string . '</>';
	}
}