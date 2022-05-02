<?php

namespace App\Http\Controllers;

use App\Exceptions\Internal\FrameworkException;
use App\Facades\Lang;
use App\Http\Requests\View\GetPhotoViewRequest;
use App\Models\Configs;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;
use Psr\Container\ContainerExceptionInterface;

class ViewController extends Controller
{
	/**
	 * View is only used when sharing a single picture.
	 *
	 * @param GetPhotoViewRequest $request
	 *
	 * @return View
	 *
	 * @throws FrameworkException
	 */
	public function view(GetPhotoViewRequest $request): View
	{
		try {
			$photo = $request->photo();
			$sizeVariant = $photo->size_variants->getMedium() ?: $photo->size_variants->getOriginal();
			$title = Configs::get_value('site_title', Config::get('defines.defaults.SITE_TITLE'));
			$rss_enable = Configs::get_value('rss_enable', '0') == '1';

			$url = config('app.url') . $request->server->get('REQUEST_URI');
			$picture = $sizeVariant->url;

			$lang = Lang::get_lang();
			$lang['language'] = Configs::get_value('lang');

			return view('view', [
				'locale' => $lang,
				'url' => $url,
				'photo' => $photo,
				'picture' => $picture,
				'title' => $title,
				'rss_enable' => $rss_enable,
			]);
		} catch (BindingResolutionException|ContainerExceptionInterface $e) {
			throw new FrameworkException('Laravel\'s container component', $e);
		}
	}
}
