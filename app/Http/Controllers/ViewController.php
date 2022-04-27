<?php

namespace App\Http\Controllers;

use App\Facades\Lang;
use App\Legacy\Legacy;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\Photo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ViewController extends Controller
{
	/**
	 * View is only used when sharing a single picture.
	 *
	 * @param Request $request
	 *
	 * @return View|RedirectResponse
	 */
	public function view(Request $request): View|RedirectResponse
	{
		$request->validate([
			'p' => 'required',
		]);

		$photoID = $request->get('p');
		if (Legacy::isLegacyModelID($photoID)) {
			$photoID = Legacy::translateLegacyPhotoID($photoID, $request);
			if ($photoID === null) {
				abort(SymfonyResponse::HTTP_NOT_FOUND);
			} else {
				return redirect()->route('view', ['p' => $photoID]);
			}
		}

		/** @var Photo $photo */
		$photo = Photo::with(['album', 'size_variants', 'size_variants.sym_links'])
			->find($photoID);

		if ($photo == null) {
			Logs::error(__METHOD__, __LINE__, 'Could not find photo in database');

			return abort(SymfonyResponse::HTTP_NOT_FOUND);
		}

		// TODO: Instead of re-coding the logic here whether an photo is visible or not, the query for a photo above, should be filtered with `PhotoAuthorisationProvider`

		// is the picture public ?
		$public = $photo->is_public || ($photo->album_id && $photo->album->is_public);

		// return 403 if not allowed
		if (!$public) {
			return abort(SymfonyResponse::HTTP_FORBIDDEN);
		}

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
	}
}
