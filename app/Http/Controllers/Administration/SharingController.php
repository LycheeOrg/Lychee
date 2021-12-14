<?php

namespace App\Http\Controllers\Administration;

use App\Actions\Sharing\ListShare;
use App\Exceptions\Internal\QueryBuilderException;
use App\Facades\AccessControl;
use App\Http\Controllers\Controller;
use App\Http\Requests\Sharing\DeleteSharingRequest;
use App\Http\Requests\Sharing\SetSharingRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class SharingController extends Controller
{
	/**
	 * Returns the list of sharing permissions wrt. the authenticated user.
	 *
	 * @param ListShare $listShare
	 *
	 * @return array
	 *
	 * @throws QueryBuilderException
	 */
	public function listSharing(ListShare $listShare): array
	{
		return $listShare->do(AccessControl::id());
	}

	/**
	 * Add a sharing between selected users and selected albums.
	 *
	 * @param SetSharingRequest $request
	 *
	 * @return void
	 *
	 * @throws QueryBuilderException
	 */
	public function add(SetSharingRequest $request): void
	{
		/** @var Collection<User> $users */
		$users = User::query()
			->whereIn('id', $request->userIDs())
			->get();

		/** @var User $user */
		foreach ($users as $user) {
			$user->shared()->sync($request->albumIDs(), false);
		}
	}

	/**
	 * Given a list of shared ID we delete them
	 * This function is the only reason why we test SharedIDs in
	 * app/Http/Middleware/UploadCheck.php.
	 *
	 * FIXME: make sure that the Lychee-front is sending the correct ShareIDs
	 *
	 * @param DeleteSharingRequest $request
	 *
	 * @return void
	 *
	 * @throws QueryBuilderException
	 */
	public function delete(DeleteSharingRequest $request): void
	{
		try {
			DB::table('user_base_album')
				->whereIn('id', $request->shareIDs())
				->delete();
		} catch (\Throwable $e) {
			throw new QueryBuilderException($e);
		}
	}
}
