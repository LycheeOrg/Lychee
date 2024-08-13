<?php

namespace App\Http\Controllers\Gallery;

use App\Actions\Sharing\Share;
use App\Constants\AccessPermissionConstants as APC;
use App\Http\Requests\Sharing\AddSharingRequest;
use App\Http\Requests\Sharing\DeleteSharingRequest;
use App\Http\Requests\Sharing\ListSharingRequest;
use App\Http\Resources\Models\AccessPermissionResource;
use App\Models\AccessPermission;
use Illuminate\Routing\Controller;

/**
 * Controller responsible for the config.
 */
class SharingController extends Controller
{
	/**
	 * @param AddSharingRequest $request
	 * @param Share             $share
	 *
	 * @return array<string|int, \App\Http\Resources\Models\AccessPermissionResource>
	 */
	public function create(AddSharingRequest $request, Share $share): array
	{
		$access_permissions = [];
		// Not optimal, but this is barely used, so who cares.
		// A better approach would be to do a massive insert in a single SQL query from the cross product.
		foreach ($request->userIds() as $user_id) {
			foreach ($request->albumIds() as $album_id) {
				$access_permissions[] = $share->do($request->perm(), $user_id, $album_id);
			}
		}

		return AccessPermissionResource::collect($access_permissions);
	}

	/**
	 * List sharing permissions.
	 *
	 * @param ListSharingRequest $request
	 *
	 * @return array<string|int, \App\Http\Resources\Models\AccessPermissionResource>
	 */
	public function list(ListSharingRequest $request): array
	{
		return AccessPermissionResource::collect(AccessPermission::query()->where(APC::BASE_ALBUM_ID, '=', $request->album()->id)->get()->all());
	}

	/**
	 * Delete sharing permissions.
	 *
	 * @param DeleteSharingRequest $request
	 *
	 * @return void
	 */
	public function delete(DeleteSharingRequest $request): void
	{
		AccessPermission::query()->where('id', '=', $request->perm()->id)->delete();
	}
}