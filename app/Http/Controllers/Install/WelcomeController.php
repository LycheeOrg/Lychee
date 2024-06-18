<?php

declare(strict_types=1);

namespace App\Http\Controllers\Install;

use App\Exceptions\Internal\FrameworkException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;

final class WelcomeController extends Controller
{
	/**
	 * @return View
	 *
	 * @throws FrameworkException
	 */
	public function view(): View
	{
		try {
			// Show separator
			return view('install.welcome', [
				'title' => 'Lychee-installer',
				'step' => 0,
			]);
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s view component', $e);
		}
	}
}
