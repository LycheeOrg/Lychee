<?php

namespace App\Actions\User;

use App\Facades\AccessControl;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\User;
use App\Notifications\PhotoAdded;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class Notify
{
	public function do(Photo $photo): void
	{
		if (Configs::get_Value('new_photos_notification', '0') !== '1') {
			return;
		}

		// The admin is always informed
		$users = new Collection([User::query()->find(0)]);
		$album = $photo->album;
		if ($album) {
			$users->push($album->shared_with);
			$users->push($album->owner);
		}

		$users = $users
			->unique('id', true)
			->whereNotNull('email')
			->where('id', '!=', AccessControl::id());

		Notification::send($users, new PhotoAdded($photo));
	}
}
