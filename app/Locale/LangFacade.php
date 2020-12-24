<?php

namespace App\Locale;

use Illuminate\Support\Facades\Facade;

class LangFacade extends Facade
{
	protected static function getFacadeAccessor()
	{
		return 'lang';
	}
}
