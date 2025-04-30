<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Install;

use App\Exceptions\InstallationFailedException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use function Safe\file_get_contents;
use function Safe\file_put_contents;

class EnvController extends Controller
{
	/**
	 * @param Request $request
	 *
	 * @return View
	 *
	 * @throws InstallationFailedException
	 */
	public function view(Request $request): View
	{
		try {
			if ($request->has('envConfig')) {
				// @codeCoverageIgnoreStart We are not testing this as this would break the tests.
				$env = str_replace("\r", '', $request->get('envConfig'));
				file_put_contents(base_path('.env'), $env, LOCK_EX);
				$exists = true;
			// @codeCoverageIgnoreEnd
			} elseif (file_exists(base_path('.env'))) {
				$env = file_get_contents(base_path('.env'));
				$exists = true;
			} else {
				// @codeCoverageIgnoreStart We cannot test this as we need the .env to be present to run the tests.
				$env = file_get_contents(base_path('.env.example'));
				$exists = false;
				// @codeCoverageIgnoreEnd
			}

			return view('install.env', [
				'title' => 'Lychee-installer',
				'step' => 3,
				'env' => $env,
				'exists' => $exists,
			]);
			// @codeCoverageIgnoreStart
		} catch (\ErrorException $e) {
			// possibly thrown by low-level methods like `file_put_contents`
			throw new InstallationFailedException('I/O error for file `.env`', $e);
		}
		// @codeCoverageIgnoreEnd
	}
}
