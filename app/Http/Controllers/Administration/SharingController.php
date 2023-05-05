<?php

namespace App\Http\Controllers\Administration;

use App\Actions\Sharing\ListShare;
use App\Constants\AccessPermissionConstants as APC;
use App\Exceptions\Internal\QueryBuilderException;
use App\Http\Requests\Sharing\AddSharesRequest;
use App\Http\Requests\Sharing\DeleteSharingRequest;
use App\Http\Requests\Sharing\ListSharingRequest;
use App\Http\Requests\Sharing\SetSharesByAlbumRequest;
use App\Http\Resources\Sharing\SharesResource;
use App\Models\AccessPermission;
use App\Models\Configs;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class SharingController extends Controller
{
	/**
	 * Returns the list of sharing permissions wrt. the authenticated user.
	 *
	 * @param ListSharingRequest $request
	 * @param ListShare          $listShare
	 *
	 * @return SharesResource
	 *
	 * @throws QueryBuilderException
	 */
	public function list(ListSharingRequest $request, ListShare $listShare): SharesResource
	{
		return $listShare->do($request->participant(), $request->owner(), $request->album());
	}

	/**
	 * Add a sharing between selected users and selected albums.
	 *
	 * @param AddSharesRequest $request
	 *
	 * @return void
	 *
	 * @throws QueryBuilderException
	 */
	public function add(AddSharesRequest $request): void
	{
		/** @var Collection<User> $users */
		$users = User::query()
			->whereIn('id', $request->userIDs())
			->get();

		/** @var User $user */
		foreach ($users as $user) {
			$user->shared()->syncWithPivotValues(
				$request->albumIDs(),
				[
					APC::GRANTS_DOWNLOAD => Configs::getValueAsBool('grants_download'),
					APC::GRANTS_FULL_PHOTO_ACCESS => Configs::getValueAsBool('grants_full_photo_access'),
				],
				false);
		}
	}

	/**
	 * Set the shares for the given album.
	 *
	 * Note: This method *sets* the shares (in contrast to *add*).
	 * This means, any user not given in the list of user IDs is removed
	 * if the album has been shared with this user before.
	 *
	 * @param SetSharesByAlbumRequest $request
	 *
	 * @return void
	 */
	public function setByAlbum(SetSharesByAlbumRequest $request): void
	{
		foreach ($request->userIDs() as $user_id) {
			AccessPermission::firstOrCreate([
				'user_id' => $user_id,
				'base_album_id' => $request->album()->id,
			])->save();
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
