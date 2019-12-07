<?php

namespace App\Metadata;

use App\Configs;
use App\ModelFunctions\JsonRequestFunctions;
use Config;

class GitRequest extends JsonRequestFunctions
{
	/**
	 * we just override the constructor,
	 * The rest is handled directly by the parent class.
	 */
	public function __construct()
	{
		parent::__construct(Config::get('urls.update.git'),
			intval(Configs::get_value('update_check_every_days', '3'), 10));
	}
}