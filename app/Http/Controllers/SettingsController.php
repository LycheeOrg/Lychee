<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers;

use App\Album;
use App\Configs;
use App\Locale\Lang;
use App\Logs;
use App\Response;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
	public function setLogin(Request $request)
	{
		$request->validate([
			'username' => 'required|string',
			'password' => 'required|string',
		]);

		$configs = Configs::get();
		$oldPassword = $request->has('oldPassword') ? $request['oldPassword'] : '';
		$oldUsername = $request->has('oldUsername') ? $request['oldUsername'] : '';

		if ($configs['password'] === '' && $configs['username'] === '') {
			Configs::set('username', bcrypt($request['username']));
			Configs::set('password', bcrypt($request['password']));

			return 'true';
		}

		if (Session::has('UserID')) {
			$id = Session::get('UserID');
			if ($id == 0) {
				if ($configs['password'] === '' || Hash::check($oldPassword, $configs['password'])) {
					Configs::set('username', bcrypt($request['username']));
					Configs::set('password', bcrypt($request['password']));

					return 'true';
				}

				return Response::error('Current password entered incorrectly!');
			}

			// this is probably sensitive to timing attacks...
			$user = User::find($id);

			if ($user == null) {
				Logs::error(__METHOD__, __LINE__, 'User ('.$id.') does not exist!');

				return Response::error('Could not find User.');
			}

			if ($user->lock) {
				Logs::notice(__METHOD__, __LINE__, 'Locked user ('.$user->username.') tried to change his identity from '.$request->ip());

				return Response::error('Locked account!');
			}

			if ($user->username == $oldUsername && Hash::check($oldPassword, $user->password)) {
				Logs::notice(__METHOD__, __LINE__, 'User ('.$user->username.') changed his identity for ('.$request['username'].') from '.$request->ip());
				$user->username = $request['username'];
				$user->password = bcrypt($request['password']);
				$user->save();

				return 'true';
			} else {
				Logs::notice(__METHOD__, __LINE__, 'User ('.$user->username.') tried to change his identity from '.$request->ip());

				return Response::error('Old username or password entered incorrectly!');
			}
		}
	}

	public function setSorting(Request $request)
	{
		$request->validate([
			'typeAlbums' => 'required|string',
			'orderAlbums' => 'required|string',
			'typePhotos' => 'required|string',
			'orderPhotos' => 'required|string',
		]);

		Configs::set('sortingPhotos_col', $request['typePhotos']);
		Configs::set('sortingPhotos_order', $request['orderPhotos']);
		Configs::set('sortingAlbums_col', $request['typeAlbums']);
		Configs::set('sortingAlbums_order', $request['orderAlbums']);

		if ('typeAlbums' == 'max_takestamp' or 'typeAlbums' == 'min_takestamp') {
			Album::reset_takestamp();
		}

		return 'true';
	}

	public function setLang(Request $request)
	{
		$request->validate([
			'lang' => 'required|string',
		]);

		$lang_available = Lang::get_lang_available();
		for ($i = 0; $i < count($lang_available); $i++) {
			if ($request['lang'] == $lang_available[$i]) {
				return (Configs::set('lang', $lang_available[$i])) ? 'true' : 'false';
			}
		}

		Logs::error(__METHOD__, __LINE__, 'Could not update settings. Unknown lang.');

		return 'false';
	}

	public function setLayout(Request $request)
	{
		$request->validate([
			'layout' => 'required|string',
		]);

		if ($request['layout'] === '0' || $request['layout'] === '1' || $request['layout'] === '2') {
			return (Configs::set('layout', $request['layout'])) ? 'true' : 'false';
		}

		Logs::error(__METHOD__, __LINE__, 'Could not find the submitted layout');

		return 'false';
	}

	public function setPublicSearch(Request $request)
	{
		$request->validate([
			'public_search' => 'required|string',
		]);

		if ($request['public_search'] == '1') {
			return (Configs::set('public_search', '1')) ? 'true' : 'false';
		}

		return (Configs::set('public_search', '0')) ? 'true' : 'false';
	}

	public function setImageOverlay(Request $request)
	{
		$request->validate([
			'image_overlay' => 'required|string',
		]);

		if ($request['image_overlay'] == '1') {
			return (Configs::set('image_overlay', '1')) ? 'true' : 'false';
		}

		return (Configs::set('image_overlay', '0')) ? 'true' : 'false';
	}

	public function setImageOverlayType(Request $request)
	{
		$overlays = ['exif', 'desc', 'takedate'];

		$request->validate([
			'image_overlay_type' => 'required|string',
		]);

		$found = false;
		$i = 0;
		while (!$found && $i < count($overlays)) {
			if ($overlays[$i] === $request['image_overlay_type']) {
				$found = true;
			}
			$i++;
		}
		if (!$found) {
			Logs::error(__METHOD__, __LINE__, 'Could not find the submitted overlay type');

			return Response::error('Could not find the submitted overlay type');
		}

		return (Configs::set('image_overlay_type', $request['image_overlay_type'])) ? 'true' : 'false';
	}

	public function setDefaultLicense(Request $request)
	{
		$request->validate([
			'license' => 'required|string',
		]);

		$licenses = [
			'none',
			'reserved',
			'CC0',
			'CC-BY',
			'CC-BY-ND',
			'CC-BY-SA',
			'CC-BY-NC',
			'CC-BY-NC-ND',
			'CC-BY-NC-SA',
		];
		$i = 0;
		while ($i < count($licenses)) {
			if ($licenses[$i] === $request['license']) {
				return (Configs::set('default_license', $request['license'])) ? 'true' : 'false';
			}
			$i++;
		}

		Logs::error(__METHOD__, __LINE__, 'Could not find the submitted license');

		return 'false';
	}

	public function setCSS(Request $request)
	{
		$request->validate(['css' => 'nullable|string']);
		$css = $request->get('css');
		$css = $css == null ? '' : $css;

		if (!Storage::put('dist/user.css', $css, 'public')) {
			return 'false';
		}

		// this is a very bad way to do it. Any improvement are welcomed.
		if (file_exists('../storage/app/dist/user.css')) {
			if (!@rename('../storage/app/dist/user.css', 'dist/user.css')) {
				Logs::error(__METHOD__, __LINE__, 'Could not move css file');

				return Response::error('Could not move css file.');
			}
		} else {
			Logs::error(__METHOD__, __LINE__, 'Could not find css file.');

			return Response::error('Could not find css file.');
		}

		return 'true';
	}

	public function getAll(Request $request)
	{
		return Configs::orderBy('cat', 'ASC')->get();
	}

	public function saveAll(Request $request)
	{
		$no_error = true;
		foreach ($request->except(['_token', 'function', '/api/Settings::saveAll']) as $key => $value) {
			$value = ($value == null) ? '' : $value;
			$no_error &= Configs::set($key, $value);
		}

		return $no_error ? 'true' : 'false';
	}
}
