<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Actions\Album\Unlock;
use App\Models\Configs;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
	private function passwordManagement(Request $request, $albumid, Unlock $unlock)
	{
		if ($request->filled('password')) {
			if (Configs::get_value('unlock_password_photos_with_url_param', '0') == '1') {
				$unlock->propagate($request['password']);
			} else {
				$unlock->do($albumid, $request['password']);
			}
		}
	}

	/**
	 * Trivial redirection.
	 *
	 * @param Request $request
	 * @param string  $albumid
	 */
	public function album(Request $request, $albumid, Unlock $unlock)
	{
		$this->passwordManagement($request, $albumid, $unlock);

		return redirect('gallery#' . $albumid);
	}

	/**
	 * Trivial redirection.
	 *
	 * @param Request $request
	 * @param string  $albumid
	 * @param string  $photoid
	 */
	public function photo(Request $request, $albumid, $photoid, Unlock $unlock)
	{
		$this->passwordManagement($request, $albumid, $unlock);

		return redirect('gallery#' . $albumid . '/' . $photoid);
	}
}
