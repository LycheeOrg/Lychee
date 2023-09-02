<?php

namespace App\View\Components;

use App\Exceptions\ConfigurationKeyMissingException;
use App\Models\Configs;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\View\Component;
use Illuminate\View\View;

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

	private string $layout;

	/**
	 * Initialize the footer once for all.
	 *
	 * @throws ConfigurationKeyMissingException
	 */
	public function __construct(string $layout = 'footer')
	{
		$this->layout = $layout;
		$this->show_socials = Configs::getValueAsBool('footer_show_social_media');
		$this->facebook = Configs::getValueAsString('sm_facebook_url');
		$this->flickr = Configs::getValueAsString('sm_flickr_url');
		$this->twitter = Configs::getValueAsString('sm_twitter_url');
		$this->instagram = Configs::getValueAsString('sm_instagram_url');
		$this->youtube = Configs::getValueAsString('sm_youtube_url');

		$this->hosted_by = __('lychee.HOSTED_WITH_LYCHEE');

		if (Configs::getValueAsBool('footer_show_copyright')) {
			$copyright_year = Configs::getValueAsString('site_copyright_begin');
			$copyright_year_end = Configs::getValueAsString('site_copyright_end');
			if ($copyright_year !== $copyright_year_end) {
				$copyright_year = $copyright_year . '-' . $copyright_year_end;
			}

			$this->copyright = sprintf(
				__('lychee.FOOTER_COPYRIGHT'),
				Configs::getValueAsString('site_owner'),
				$copyright_year
			);
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
		return view('components.' . $this->layout);
	}
}
