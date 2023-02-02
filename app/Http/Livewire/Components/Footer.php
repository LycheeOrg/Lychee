<?php

namespace App\Http\Livewire\Components;

use App\Exceptions\ConfigurationKeyMissingException;
use App\Models\Configs;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\View\View;
use Livewire\Component;

/**
 * This is the bottom of the page.
 * We provides socials etc...
 */
class Footer extends Component
{
	public bool $show_socials;
	public ?string $hosted_by = null;
	public ?string $copyright = null;
	public string $facebook;
	public string $flickr;
	public string $twitter;
	public string $instagram;
	public string $youtube;
	public string $additional_footer_text;
	public string $class = 'vflex-item-rigid animate animate-up';
	public string $html_id = 'lychee_footer';

	/**
	 * Initialize the footer once for all.
	 *
	 * @return void
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function mount(): void
	{
		$this->show_socials = Configs::getValueAsBool('footer_show_social_media');
		$this->facebook = Configs::getValueAsString('sm_facebook_url');
		$this->flickr = Configs::getValueAsString('sm_flickr_url');
		$this->twitter = Configs::getValueAsString('sm_twitter_url');
		$this->instagram = Configs::getValueAsString('sm_instagram_url');
		$this->youtube = Configs::getValueAsString('sm_youtube_url');

		$this->hosted_by = __('lychee.HOSTED_WITH_LYCHEE');

		if (Configs::getValueAsBool('footer_show_copyright')) {
			/** @var string $footer_text */
			$footer_text = __('lychee.FOOTER_COPYRIGHT');
			$this->copyright = sprintf(
				$footer_text,
				Configs::getValueAsString('site_owner'),
				Configs::getValueAsInt('site_copyright_end'));
		}

		$this->additional_footer_text = Configs::getValueAsString('footer_additional_text');
	}

	/**
	 * Render component.
	 *
	 * @return View
	 *
	 * @throws BindingResolutionException
	 */
	public function render(): View
	{
		return view('livewire.components.footer');
	}
}
