<?php

namespace App\Http\Controllers\Administration;

use App\Actions\Sharing\ListShare;
use App\Facades\AccessControl;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SharingController extends Controller
{
	/**
	 * Return the list of current sharing rights.
	 *
	 * @return array
	 */
	public function listSharing(ListShare $listShare)
	{
		return $listShare->do(AccessControl::id());
	}

	/**
	 * Add a sharing between selected users and selected albums.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function add(Request $request)
	{
		$request->validate([
			'UserIDs' => 'string|required',
			'albumIDs' => 'required',
		]);

		if (is_int($request['albumIDs'])) {
			$albumIDs = [$request['albumIDs']];
		} else {
			$albumIDs = explode(',', $request['albumIDs']);
			array_walk($albumIDs, function (&$value) {
				$value = intval($value);
			});
		}

		$users = User::query()->whereIn('id', explode(',', $request['UserIDs']))->get();

		/** @var User $user */
		foreach ($users as $user) {
			$user->shared()->sync($albumIDs, false);
		}

		return 'true';
	}

	/**
	 * Given a list of shared ID we delete them
	 * This function is the only reason why we test SharedIDs in
	 * app/Http/Middleware/UploadCheck.php.
	 *
	 * FIXME: make sure that the Lychee-front is sending the correct ShareIDs
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function delete(Request $request)
	{
		$request->validate([
			'ShareIDs' => 'string|required',
		]);

		DB::table('user_album')
			->whereIn('id', explode(',', $request['ShareIDs']))->delete();

		return 'true';
	}
}
