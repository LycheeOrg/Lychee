<?php

namespace App\Mail;

use App\Models\Configs;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PhotosAdded extends Mailable
{
	use Queueable;
	use SerializesModels;

	public $photos;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct($photos)
	{
		$this->photos = $photos;
		$this->settings = Configs::get();
	}

	/**
	 * Build the message.
	 *
	 * @return $this
	 */
	public function build()
	{
		return $this->markdown('emails.photos-added', [
			'title' => $this->settings['site_title'],
		]);
	}
}
