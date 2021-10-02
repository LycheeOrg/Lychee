<?php

namespace App\Http\Controllers;

use App\Actions\Album\Unlock;
use App\Contracts\LycheeException;
use App\Models\Configs;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
	/**
	 * @throws ModelNotFoundException
	 * @throws LycheeException
	 */
	private function passwordManagement(Request $request, int $albumID, Unlock $unlock): void
	{
		if ($request->filled('password')) {
			if (Configs::get_value('unlock_password_photos_with_url_param', '0') == '1') {
				$unlock->propagate($request['password']);
			} else {
				$unlock->do($albumID, $request['password']);
			}
		}
	}

	/**
	 * Trivial redirection.
	 *
	 * @param Request $request
	 * @param int     $albumID
	 * @param Unlock  $unlock
	 *
	 * @return RedirectResponse
	 *
	 * @throws LycheeException
	 * @throws ModelNotFoundException
	 */
	public function album(Request $request, int $albumID, Unlock $unlock): RedirectResponse
	{
		$this->passwordManagement($request, $albumID, $unlock);

		return redirect('gallery#' . $albumID);
	}

	/**
	 * Trivial redirection.
	 *
	 * @param Request $request
	 * @param int     $albumID
	 * @param int     $photoID
	 * @param Unlock  $unlock
	 *
	 * @return RedirectResponse
	 *
	 * @throws LycheeException
	 * @throws ModelNotFoundException
	 */
	public function photo(Request $request, int $albumID, int $photoID, Unlock $unlock): RedirectResponse
	{
		$this->passwordManagement($request, $albumID, $unlock);

		return redirect('gallery#' . $albumID . '/' . $photoID);
	}
}
