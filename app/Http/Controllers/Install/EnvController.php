<?php

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EnvController extends Controller
{
	/**
	 * @return View
	 */
	public function view(Request $request)
	{
		$env = '';
		$exists = false;

		if (file_exists(base_path('.env'))) {
			$env = file_get_contents(base_path('.env'));
			$exists = true;
		} else {
			// @codeCoverageIgnoreStart
			$env = file_get_contents(base_path('.env.example'));
			$exists = false;
			// @codeCoverageIgnoreEnd
		}

		if ($request->has('envConfig')) {
			$env = str_replace("\r", '', $request->get('envConfig'));
			file_put_contents(base_path('.env'), $env, LOCK_EX);
			$exists = true;
		}

		return view('install.env', [
			'title' => 'Lychee-installer',
			'step' => 3,
			'env' => $env,
			'exists' => $exists,
		]);
	}
}