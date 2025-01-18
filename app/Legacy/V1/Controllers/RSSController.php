<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Controllers;

use App\Actions\RSS\Generate;
use App\Contracts\Exceptions\LycheeException;
use App\Exceptions\ConfigurationException;
use App\Models\Configs;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Spatie\Feed\FeedItem;

/**
 * @codeCoverageIgnore Legacy stuff
 */
final class RSSController extends Controller
{
	/**
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
