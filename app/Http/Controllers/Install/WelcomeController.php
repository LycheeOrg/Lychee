<?php

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;

final class WelcomeController extends Controller
{
	/**
	 * @return View
	 */
	public function view()
	{
		// Show separator
		return view('install.welcome', [
			'title' => 'Lychee-installer',
			'step' => 0,
		]);
	}
}