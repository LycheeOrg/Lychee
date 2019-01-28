<?php

namespace App\Http\Controllers;

use App\Configs;
use App\Photo;
use Illuminate\Support\Facades\Config;


class FrameController extends Controller
{
	/**
	 * @return false|string
	 */
	function init()
	{
		if (Configs::get_value('Mod_Frame') != '1') {
			return redirect()->route('home');
		}

		$photo = Photo::where('star', '=', 1)->inRandomOrder()->first();

		if ($photo == null) {
			return redirect()->route('home');
		}

		$thumb = Config::get('defines.urls.LYCHEE_URL_UPLOADS_THUMB').$photo->thumbUrl;
		if ($photo->medium == '1') {
			$url = Config::get('defines.urls.LYCHEE_URL_UPLOADS_MEDIUM').$photo->url;
		}
		else {
			$url = Config::get('defines.urls.LYCHEE_URL_UPLOADS_BIG').$photo->url;
		}

		return view('frame', [
			'url'   => $url,
			'thumb' => $thumb
		]);

	}

}