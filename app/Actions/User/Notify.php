<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\User;

use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Configs;
use App\Models\Photo;
use App\Models\User;
use App\Notifications\PhotoAdded;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\MultipleRecordsFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class Notify
{
	/**
	 * Notify users that a new photo has been uploaded.
	 *
	 * @param Photo $photo
	 *
	 * @return void
	 *
	 * @throws ConfigurationKeyMissingException
	 * @throws QueryBuilderException
	 * @throws ModelNotFoundException
	 * @throws MultipleRecordsFoundException
	 */
	public function do(Photo $photo): void
	{
		if (!Configs::getValueAsBool('new_photos_notification')) {
			return;
		}

		// Admin user is always notified
		$users = User::query()->where('may_administrate', '=', true)->get();

		$album = $photo->album;
		if ($album !== null) {
			$users = $users->concat($album->shared_with);
			$users->push($album->owner);
		}

		$users = $users
			->unique('id', true)
			->whereNotNull('email')
			->where('id', '!=', Auth::id());

		Notification::send($users, new PhotoAdded($photo));
	}
}
