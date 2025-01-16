<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Notifications;

use App\Models\Photo;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PhotoAdded extends Notification
{
	use Queueable;

	protected Photo $photo;

	/**
	 * Create a new notification instance.
	 *
	 * @return void
	 */
	public function __construct(Photo $photo)
	{
		$this->photo = $photo;
	}

	/**
	 * Get the notification's delivery channels.
	 *
	 * @param mixed $notifiable
	 *
	 * @return array<string>
	 */
	public function via($notifiable)
	{
		return ['database'];
	}

	/**
	 * Get the array representation of the notification.
	 *
	 * @param mixed $notifiable
	 *
	 * @return array<string,string>
	 */
	public function toArray($notifiable)
	{
		return [
			'id' => $this->photo->id,
		];
	}
}
