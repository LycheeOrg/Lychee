<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers;

use App\Actions\RSS\Generate;
use App\Contracts\Exceptions\LycheeException;
use App\Exceptions\ConfigurationException;
use App\Models\Configs;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Spatie\Feed\FeedItem;

class RSSController extends Controller
{
	/**
	 * Get the RSS Feed.
	 *
	 * @param Generate $generate
	 *
	 * @return Collection<int,FeedItem>
	 *
	 * @throws LycheeException
	 */
	public function getRSS(Generate $generate): Collection
	{
		if (!Configs::getValueAsBool('rss_enable')) {
			throw new ConfigurationException('RSS is disabled by configuration');
		}

		return $generate->do();
	}
}
