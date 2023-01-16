<?php

namespace App\Http\Livewire\Components;

use App\Facades\Lang;
use App\Models\Configs;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\View\View;
use Livewire\Component;

/**
 * This is the "start" page of the gallery
 * Integrate the list of all albums at top level.
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


	public function mount() {
		$this->show_socials = Configs::getValueAsBool('footer_show_social_media');
		$this->facebook = Configs::getValueAsString('sm_facebook_url');
		$this->flickr = Configs::getValueAsString('sm_flickr_url');
		$this->twitter = Configs::getValueAsString('sm_twitter_url');
		$this->instagram = Configs::getValueAsString('sm_instagram_url');
		$this->youtube = Configs::getValueAsString('sm_youtube_url');

		$this->hosted_by = Lang::get('HOSTED_WITH_LYCHEE');

		if (Configs::getValueAsBool('footer_show_copyright')) {
			$this->copyright = sprintf(
				Lang::get('FOOTER_COPYRIGHT'),
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
