<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Actions\User;

use App\Constants\AccessPermissionConstants as APC;
use App\Constants\PhotoAlbum as PA;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Internal\QueryBuilderException;
use App\Models\Album;
use App\Models\Photo;
use App\Models\User;
use App\Notifications\PhotoAdded;
use App\Repositories\ConfigManager;
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
		$config_manager = resolve(ConfigManager::class);
		if (!$config_manager->getValueAsBool('new_photos_notification')) {
			return;
		}

		// Admin user is always notified
		$users = User::query()->where('may_administrate', '=', true)->get();

		$albums = Album::query()->without(['thumbs', 'statistics', 'cover', 'min_privilege_cover', 'max_privilege_cover'])->join(PA::PHOTO_ALBUM, PA::ALBUM_ID, '=', 'album.id')
			->where(PA::PHOTO_ID, '=', $photo->id)
			->get();

		if ($albums->count() > 0) {
			$shared_with = User::query()->join(APC::ACCESS_PERMISSIONS, APC::USER_ID, '=', 'user.id')
				->whereIn(APC::BASE_ALBUM_ID, $albums->pluck('id'))
				->get();
			$users->push(...$shared_with->all());
			$users->push(...$albums->map(fn (Album $a) => $a->owner)->all());
		}

		$users = $users
			->unique('id', true)
			->whereNotNull('email')
			->where('id', '!=', Auth::id());

		Notification::send($users, new PhotoAdded($photo));
	}
}
