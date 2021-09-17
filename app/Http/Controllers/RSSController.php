<?php

namespace App\Http\Controllers;

use App\Actions\RSS\Generate;
use App\Models\Configs;
use Illuminate\Support\Collection;

class RSSController extends Controller
{
	/**
	 * @return Collection
	 */
	public function getRSS(Generate $generate)
	{
		if (Configs::get_value('rss_enable', '0') != '1') {
			abort(404);
		}

		return $generate->do();
	}
}
