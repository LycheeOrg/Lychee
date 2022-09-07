<?php

namespace App\Http\Controllers\Administration;

use App\Actions\Sharing\ListShare;
use App\DTO\Shares;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\UnauthenticatedException;
use App\Exceptions\UnauthorizedException;
use App\Http\Requests\Sharing\DeleteSharingRequest;
use App\Http\Requests\Sharing\SetSharingRequest;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class SharingController extends Controller
{
	/**
	 * Returns the list of sharing permissions wrt. the authenticated user.
	 *
	 * @param ListShare $listShare
	 *
	 * @return Shares
	 *
	 * @throws QueryBuilderException
	 * @throws UnauthenticatedException
	 * @throws UnauthorizedException
	 */
	public function list(ListShare $listShare): Shares
	{
		/** @var int */
		$userId = Auth::id() ?? throw new UnauthenticatedException();

		// TODO: move this to Request authorization.
		// Note: This test is part of the request validation for the other
		// methods of this class.
		if (!Gate::check(UserPolicy::MAY_UPLOAD, User::class)) {
			throw new UnauthorizedException('Upload privilege required');
		}

		return $listShare->do($userId);
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
