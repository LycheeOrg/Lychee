<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Lang extends Facade
{
	protected static function getFacadeAccessor()
	{
		return 'lang';
	}
}
