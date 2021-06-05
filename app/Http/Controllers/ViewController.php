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
	 * Just the constructor
	 * This also shows how to apply a middlewear directly in a controller.
	 *
	 * ViewController constructor.
	 */
	public function __construct()
	{
		$this->middleware([]);
	}

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
		$photo = Photo::find($request->get('p'));

		if ($photo == null) {
			Logs::error(__METHOD__, __LINE__, 'Could not find photo in database');

			return abort(404);
		}

		// is the picture public ?
		$public = $photo->public == '1';

		// is the album (if exist) public ?
		if ($photo->album_id != null) {
			$public = $photo->album->public == '1' || $public;
		}
		// return 403 if not allowed
		if (!$public) {
			return abort(403);
		}

		// TODO: Refactor this
		// Don't build the URL and paths manually, but use the appropriate
		// methods of $photo.
		// Don't rely on hard-coded path prefixes like "medium" or "big".
		// Hopefully, this code goes away with the new Livewire frontend
		if ($photo->size_variants->getMedium()) {
			$dir = 'medium';
		} else {
			$dir = 'big';
		}

		$title = Configs::get_value('site_title', Config::get('defines.defaults.SITE_TITLE'));
		$rss_enable = Configs::get_value('rss_enable', '0') == '1';

		$url = config('app.url') . $request->server->get('REQUEST_URI');
		$picture = config('app.url') . '/uploads/' . $dir . '/' . $photo->filename;

		return view('view', [
			'url' => $url,
			'photo' => $photo,
			'picture' => $picture,
			'title' => $title,
			'rss_enable' => $rss_enable,
		]);
	}
}
