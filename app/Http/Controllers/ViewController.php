<?php

namespace App\Http\Controllers;

use App\Exceptions\Internal\FrameworkException;
use App\Facades\Lang;
use App\Http\Requests\View\GetPhotoViewRequest;
use App\Models\Configs;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Psr\Container\ContainerExceptionInterface;

class ViewController extends Controller
{
	/**
	 * View is only used when sharing a single picture.
	 *
	 * @param GetPhotoViewRequest $request
	 *
	 * @return RedirectResponse
	 *
	 * @throws FrameworkException
	 */
	public function view(GetPhotoViewRequest $request): RedirectResponse
	{
		try {
			$photo = $request->photo();
			return redirect('/#view/' . $photo->id);
			/*
			$sizeVariant = $photo->size_variants->getMedium() ?? $photo->size_variants->getOriginal();
			$title = Configs::getValueAsString('site_title');
			$rss_enable = Configs::getValueAsBool('rss_enable');

			$url = config('app.url') . $request->server->get('REQUEST_URI');
			$picture = $sizeVariant->url;

			$lang = Lang::get_lang();
			$lang['language'] = Configs::getValueAsString('lang');

			return view('view', [
				'locale' => $lang,
				'url' => $url,
				'photo' => $photo,
				'picture' => $picture,
				'title' => $title,
				'rss_enable' => $rss_enable,
			]);
			*/
		} catch (BindingResolutionException|ContainerExceptionInterface $e) {
			throw new FrameworkException('Laravel\'s container component', $e);
		}
	}
}
