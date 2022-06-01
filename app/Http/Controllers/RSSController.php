<?php

namespace App\Http\Controllers;

use App\Actions\RSS\Generate;
use App\Contracts\LycheeException;
use App\Exceptions\ConfigurationException;
use App\Models\Configs;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;

class RSSController extends Controller
{
	/**
	 * @param Generate $generate
	 *
	 * @return Collection
	 *
	 * @throws LycheeException
	 */
	public function getRSS(Generate $generate): Collection
	{
		if (Configs::getValueAsBool('rss_enable', false) == false) {
			throw new ConfigurationException('RSS is disabled by configuration');
		}

		return $generate->do();
	}
}
