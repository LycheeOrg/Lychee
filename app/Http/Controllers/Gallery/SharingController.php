<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Gallery;

use App\Actions\Sharing\Propagate;
use App\Actions\Sharing\Share;
use App\Constants\AccessPermissionConstants as APC;
use App\Exceptions\Internal\LycheeLogicException;
use App\Http\Requests\Sharing\AddSharingRequest;
use App\Http\Requests\Sharing\DeleteSharingRequest;
use App\Http\Requests\Sharing\EditSharingRequest;
use App\Http\Requests\Sharing\ListAllSharingRequest;
use App\Http\Requests\Sharing\ListSharingRequest;
use App\Http\Requests\Sharing\PropagateSharingRequest;
use App\Http\Resources\Models\AccessPermissionResource;
use App\Models\AccessPermission;
use App\Models\Album;
use App\Models\BaseAlbumImpl;
use Illuminate\Routing\Controller;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Controller responsible for the config.
 */
class SharingController extends Controller
{
	/**
	 * Create a new Sharing link between a user and an album.
	 *
	 * @param AddSharingRequest $request
	 * @param Share             $share
	 *
	 * @return array<string|int,AccessPermissionResource>
	 */
	public function create(AddSharingRequest $request, Share $share): array
	{
		// delete any already created.
		AccessPermission::whereIn('user_id', $request->userIds())
			->whereIn('base_album_id', $request->albumIds())
			->delete();

		$access_permissions = [];
		// Not optimal, but this is barely used, so who cares.
		// A better approach would be to do a massive insert in a single SQL query from the cross product.
		foreach ($request->userIds() as $user_id) {
			foreach ($request->albumIds() as $album_id) {
				$access_permissions[] = $share->do($request->permResource(), $user_id, $album_id);
			}
		}

		return AccessPermissionResource::collect($access_permissions);
	}

	/**
	 * Edit sharing permissions.
	 *
	 * @param EditSharingRequest $request
	 *
	 * @return AccessPermissionResource
	 */
	public function edit(EditSharingRequest $request): AccessPermissionResource
	{
		$perm = $request->perm();
		$perm->update([
			'grants_full_photo_access' => $request->permResource()->grants_full_photo_access,
			'grants_download' => $request->permResource()->grants_download,
			'grants_upload' => $request->permResource()->grants_upload,
			'grants_edit' => $request->permResource()->grants_edit,
			'grants_delete' => $request->permResource()->grants_delete,
		]);

		return AccessPermissionResource::fromModel($perm);
	}

	/**
	 * List sharing permissions.
	 *
	 * @param ListSharingRequest $request
	 *
	 * @return Collection<string|int, \App\Http\Resources\Models\AccessPermissionResource>
	 */
	public function list(ListSharingRequest $request): Collection
	{
		$query = AccessPermission::with(['album', 'user']);
		$query = $query->whereNotNull(APC::USER_ID);
		$query = $query->where(APC::BASE_ALBUM_ID, '=', $request->album()->id);

		return AccessPermissionResource::collect($query->get());
	}

	/**
	 * List all sharing permissions.
	 *
	 * @param ListAllSharingRequest $request
	 *
	 * @return Collection<string|int, \App\Http\Resources\Models\AccessPermissionResource>
	 */
	public function listAll(ListAllSharingRequest $request): Collection
	{
		$query = AccessPermission::with(['album', 'user']);
		$query = $query->when(
			!Auth::user()->may_administrate,
			fn ($q) => $q->whereIn('base_album_id', BaseAlbumImpl::select('id')
						->where('owner_id', '=', Auth::id())));
		$query = $query->whereNotNull('user_id');
		$query = $query->orderBy('base_album_id', 'asc');

		return AccessPermissionResource::collect($query->get());
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

	/**
	 * Propagate sharing permissions.
	 *
	 * @param PropagateSharingRequest $request
	 *
	 * @return void
	 */
	public function propagate(PropagateSharingRequest $request, Propagate $propagate): void
	{
		$album = $request->album();
		if (!$album instanceof Album) {
			throw new LycheeLogicException('Only albums can have any descandants.');
		}

		if ($request->shallOverride) {
			$propagate->overwrite($album);
		} else {
			$propagate->update($album);
		}
	}
}