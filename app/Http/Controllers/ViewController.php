<?php

namespace App\Http\Controllers;

use App\Exceptions\ConfigurationException;
use App\Exceptions\Internal\FrameworkException;
use App\Http\Requests\View\GetPhotoViewRequest;
use App\Legacy\Legacy;
use App\Models\Configs;
use App\Models\Photo;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;
use Psr\Container\ContainerExceptionInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class ViewController extends Controller
{
	/**
	 * View is only used when sharing a single picture.
	 *
	 * @param GetPhotoViewRequest $request
	 *
	 * @return View|RedirectResponse
	 *
	 * @throws ModelNotFoundException
	 * @throws FrameworkException
	 * @throws ConfigurationException
	 */
	public function view(GetPhotoViewRequest $request): View|RedirectResponse
	{
		try {
			$photoID = $request->photoID();
			if (Legacy::isLegacyModelID($photoID)) {
				$photoID = Legacy::translateLegacyPhotoID($photoID, $request);
				if ($photoID) {
					return redirect()->route('view', ['p' => $photoID]);
				}
			}

			/** @var Photo $photo */
			$photo = Photo::with(['album', 'size_variants', 'size_variants.sym_links'])
				->findOrFail($photoID);

			$sizeVariant = $photo->size_variants->getMedium() ?: $photo->size_variants->getOriginal();

			$title = Configs::get_value('site_title', Config::get('defines.defaults.SITE_TITLE'));
			$rss_enable = Configs::get_value('rss_enable', '0') == '1';

			$url = config('app.url') . $request->server->get('REQUEST_URI');
			$picture = $sizeVariant->url;

			return view('view', [
				'url' => $url,
				'photo' => $photo,
				'picture' => $picture,
				'title' => $title,
				'rss_enable' => $rss_enable,
			]);
		} catch (BindingResolutionException|ContainerExceptionInterface $e) {
			throw new FrameworkException('Laravel\'s container component', $e);
		} catch (RouteNotFoundException $e) {
			throw new FrameworkException('Symfony\'s redirection component', $e);
		}
	}
}
