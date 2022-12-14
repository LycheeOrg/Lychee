<?php

namespace App\Metadata\Json;

use App\Models\Configs;
use Illuminate\Support\Facades\Config;

class TagsRequest extends JsonRequestFunctions
{
	/**
	 * we just override the constructor,
	 * The rest is handled directly by the parent class.
	 */
	public function __construct()
	{
		$this->init(
			Config::get('urls.update.git.tags'),
			Configs::getValueAsInt('update_check_every_days')
		);
	}
}
