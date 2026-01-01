<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Mail;

use App\Repositories\ConfigManager;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PhotosAdded extends Mailable
{
	use Queueable;
	use SerializesModels;

	/** @var array<string, array<string, array<string, array<string, string|null>>|string>> */
	protected array $photos;
	protected string $title;

	/**
	 * Create a new message instance.
	 *
	 * @param array<string, array<string, array<string, array<string, string|null>>|string>> $photos
	 *
	 * @return void
	 */
	public function __construct(array $photos)
	{
		$config_manager = app(ConfigManager::class);
		$this->photos = $photos;
		$this->title = $config_manager->getValueAsString('site_title');
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build(): self
	{
		return $this->markdown('emails.photos-added', [
			'title' => $this->title,
			'photos' => $this->photos,
		]);
	}
}
