<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Models\Configs;
use App\Models\Logs;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\View\View;

class ViewController extends Controller
{
	/**
	 * View is only used when sharing a single picture.
	 *
	 * @param Request $request
	 *
	 * @return View|void
	 */
	public function view(Request $request)
	{
		$request->validate([
			'p' => 'required',
		]);

		/** @var Photo $photo */
		$photo = Photo::with(['album', 'size_variants', 'size_variants.sym_links'])
			->find($request->get('p'));

		if ($photo == null) {
			Logs::error(__METHOD__, __LINE__, 'Could not find photo in database');

			return abort(404);
		}

		// TODO: Instead of re-coding the logic here whether an photo is visible or not, the query for a photo above, should be filtered with `PhotoAuthorisationProvider`

		// is the picture public ?
		$public = $photo->is_public || ($photo->album_id && $photo->album->is_public);

		// return 403 if not allowed
		if (!$public) {
			return abort(403);
		}

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
	}
}
