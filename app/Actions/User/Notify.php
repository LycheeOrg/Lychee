<?php

namespace App\Actions\User;

use App\Models\Configs;
use App\Models\Photo;
use App\Models\User;
use App\Notifications\PhotoAdded;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class Notify
{
	public function do(Photo $photo): void
	{
		if (!Configs::getValueAsBool('new_photos_notification')) {
			return;
		}

		// The admin is always informed
		$users = new Collection([User::query()->find(0)]);
		$album = $photo->album;
		if ($album !== null) {
			// phpstan throws this error
			// Parameter #1 ...$values of method Illuminate\Support\Collection<int,Illuminate\Database\Eloquent\Model|null>::push() expects Illuminate\Database\Eloquent\Model|null, Illuminate\Database\Eloquent\Collection given.
			// However the signature is push(... $values) which also consider collection/array...
			// @phpstan-ignore-next-line
			$users->push($album->shared_with);
			$users->push($album->owner);
		}

		$users = $users
			->unique('id', true)
			->whereNotNull('email')
			->where('id', '!=', Auth::id());

		Notification::send($users, new PhotoAdded($photo));
	}
}
