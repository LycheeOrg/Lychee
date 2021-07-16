<?php

namespace App\Actions\User;

use App\Facades\AccessControl;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\User;
use App\Notifications\PhotoAdded;
use Illuminate\Support\Facades\Notification;

class Notify
{
	public function do(Photo $request, $album_id = null)
	{
		$settings = Configs::get();

		if ($settings['new_photos_notification']) {
			if ($album_id) {
				$album = Album::find($album_id);
			} else {
				$album = Album::find($request->album_id);
			}

			$album_users = $album->shared_with;

			$owner = User::find($album->owner_id);
			$album_users->push($owner);

			if ($album->owner_id != 0) {
				$admin = User::find(0);
				$album_users->push($admin);
			}

			$album_users = $album_users->unique()
										->whereNotNull('email')
										->where('id', '!=', AccessControl::id());

			return Notification::send($album_users, new PhotoAdded($request));
		} else {
			return true;
		}
	}
}
