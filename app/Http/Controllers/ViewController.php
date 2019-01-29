<?php

namespace App\Http\Controllers;


use App\Logs;
use App\Photo;
use Illuminate\Http\Request;

class ViewController extends Controller
{

	public function __construct()
	{
		$this->middleware([]);
	}



	static function sport($port)
	{
		if ($port == 80) {
			return '';
		}
		if ($port == 443) {
			return '';
		}
		return ':'.$port;
	}



	public function view(Request $request)
	{

		$request->validate([
			'p' => 'required'
		]);

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


		if ($photo->medium == '1') {
			$dir = 'medium';
		}
		else {
			$dir = 'big';
		}

		$parseUrl = parse_url(env('APP_URL').$request->server->get('REQUEST_URI'));
//		dd($parseUrl);
		$url = env('APP_URL').$request->server->get('REQUEST_URI');
//		$picture  = '//' . $request->server->get('HTTP_HOST') . $parseUrl['path'] . '/../uploads/' . $dir . '/' . $photo->url;
		$picture = env('APP_URL').'/uploads/'.$dir.'/'.$photo->url;
//		$picture  = $parseUrl['host'] . '/uploads/' . $dir . '/' . $photo->url;


		return view('view', [
			'url'     => $url,
			'photo'   => $photo,
			'picture' => $picture,

		]);
	}


}