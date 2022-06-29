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

	protected array $photos;
	protected string $title;

	/**
	 * Create a new message instance.
	 *
	 * @return void
	 */
	public function __construct(array $photos)
	{
		$this->photos = $photos;
		$this->title = Configs::getValueAsString('site_title');
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
