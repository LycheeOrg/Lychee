<?php

/** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers\Administration;

use App\Actions\Settings\Login;
use App\Assets\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequests\UsernamePasswordRequest;
use App\Models\Configs;
use App\Models\Logs;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Lang;

class SettingsController extends Controller
{
	/**
	 * Set the Login information of the Lychee configuration
	 * Either they are not already set and we directly bcrypt the parameters
	 * or the current username and password are compared and changed if successful.
	 *
	 * To be noted this function will change the CONFIG table if used by admin
	 * or the USER table if used by any other user
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function setLogin(UsernamePasswordRequest $request, Login $login)
	{
		return $login->do($request) ? 'true' : 'false';
	}

	/**
	 * Define the default sorting type.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function setSorting(Request $request)
	{
		$validated = $request->validate([
			'typeAlbums' => 'required|string',
			'orderAlbums' => 'required|string',
			'typePhotos' => 'required|string',
			'orderPhotos' => 'required|string',
		]);

		Configs::set('sorting_Photos_col', $validated['typePhotos']);
		Configs::set('sorting_Photos_order', $validated['orderPhotos']);
		Configs::set('sorting_Albums_col', $validated['typeAlbums']);
		Configs::set('sorting_Albums_order', $validated['orderAlbums']);

		return 'true';
	}

	/**
	 * Set the lang used by the Lychee installation.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function setLang(Request $request)
	{
		$validated = $request->validate(['lang' => 'required|string']);

		foreach (Lang::get_lang_available() as $lang) {
			if ($validated['lang'] == $lang) {
				return Configs::set('lang', $lang) ? 'true' : 'false';
			}
		}

		Logs::error(__METHOD__, __LINE__, 'Could not update settings. Unknown lang.');

		return 'false';
	}

	/**
	 * Set the layout of the albums
	 * 0: squares
	 * 1: flickr justified
	 * 2: flickr unjustified.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function setLayout(Request $request)
	{
		$validated = $request->validate(['layout' => 'required|string']);

		return Configs::set('layout', $validated['layout']) ? 'true' : 'false';
	}

	/**
	 * Set the dropbox key for the API.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function setDropboxKey(Request $request)
	{
		$validated = $request->validate(['key' => 'string|nullable']);

		return Configs::set('dropbox_key', $validated['key']) ? 'true' : 'false';
	}

	/**
	 * Allow public user to use the search function.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function setPublicSearch(Request $request)
	{
		$validated = $request->validate(['public_search' => 'required|string']);

		if ($validated['public_search'] == '1') {
			return Configs::set('public_search', '1') ? 'true' : 'false';
		}

		return Configs::set('public_search', '0') ? 'true' : 'false';
	}

	/**
	 * Show image overlay by default or not
	 * (white text in the bottom right corner).
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function setImageOverlay(Request $request)
	{
		$validated = $request->validate(['image_overlay' => 'required|string']);

		if ($validated['image_overlay'] == '1') {
			return Configs::set('image_overlay', '1') ? 'true' : 'false';
		}

		return Configs::set('image_overlay', '0') ? 'true' : 'false';
	}

	/**
	 * Show NSFW albums by default or not.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function setNSFWVisible(Request $request)
	{
		$validated = $request->validate(['nsfw_visible' => 'required|string']);

		if ($validated['nsfw_visible'] == '1') {
			return Configs::set('nsfw_visible', '1') ? 'true' : 'false';
		}

		return Configs::set('nsfw_visible', '0') ? 'true' : 'false';
	}

	/**
	 * Select the image overlay used:
	 * exif: exif information
	 * desc: description of the photo
	 * takedate: date of the photo (and dimensions?).
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function setImageOverlayType(Request $request)
	{
		$validated = $request->validate(['image_overlay_type' => 'required|string']);

		return Configs::set('image_overlay_type', $validated['image_overlay_type']) ? 'true' : 'false';
	}

	/**
	 * Define the default license of the pictures.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function setDefaultLicense(Request $request)
	{
		$validated = $request->validate(['license' => 'required|string']);

		foreach (Helpers::get_all_licenses() as $license) {
			if ($license === $validated['license']) {
				return Configs::set('default_license', $license) ? 'true' : 'false';
			}
		}

		Logs::error(__METHOD__, __LINE__, 'Could not find the submitted license');

		return 'false';
	}

	/**
	 * Enable display of photo coordinates on map.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function setMapDisplay(Request $request)
	{
		$request->validate(['map_display' => 'required|string']);

		if ($request['map_display'] == '1') {
			return Configs::set('map_display', '1') ? 'true' : 'false';
		}

		return Configs::set('map_display', '0') ? 'true' : 'false';
	}

	/**
	 * Enable display of photos on map for public albums.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function setMapDisplayPublic(Request $request)
	{
		$request->validate(['map_display_public' => 'required|string']);

		if ($request['map_display_public'] == '1') {
			return Configs::set('map_display_public', '1') ? 'true' : 'false';
		}

		return Configs::set('map_display_public', '0') ? 'true' : 'false';
	}

	/**
	 * Enable display of photo direction on map.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function setMapDisplayDirection(Request $request)
	{
		$request->validate(['map_display_direction' => 'required|string']);

		if ($request['map_display_direction'] == '1') {
			return Configs::set('map_display_direction', '1') ? 'true' : 'false';
		}

		return Configs::set('map_display_direction', '0') ? 'true' : 'false';
	}

	/**
	 * Set provider of OSM map tiles.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function setMapProvider(Request $request)
	{
		$request->validate(['map_provider' => 'required|string']);

		return Configs::set('map_provider', $request['map_provider']) ? 'true' : 'false';
	}

	/**
	 * Enable display of photos of subalbums on map.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function setMapIncludeSubalbums(Request $request)
	{
		$request->validate(['map_include_subalbums' => 'required|string']);

		if ($request['map_include_subalbums'] == '1') {
			return Configs::set('map_include_subalbums', '1') ? 'true' : 'false';
		}

		return Configs::set('map_include_subalbums', '0') ? 'true' : 'false';
	}

	/**
	 * Enable decoding of GPS data into location names.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function setLocationDecoding(Request $request)
	{
		$request->validate(['location_decoding' => 'required|string']);

		return Configs::set('location_decoding', $request['location_decoding']) ? 'true' : 'false';
	}

	/**
	 * Enable display of location name.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function setLocationShow(Request $request)
	{
		$request->validate(['location_show' => 'required|string']);

		return Configs::set('location_show', $request['location_show']) ? 'true' : 'false';
	}

	/**
	 * Enable display of location name for public albums.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function setLocationShowPublic(Request $request)
	{
		$request->validate(['location_show_public' => 'required|string']);

		return Configs::set(
			'location_show_public',
			$request['location_show_public']
		) ? 'true' : 'false';
	}

	/**
	 * take the css input text and put it into dist/user.css
	 * this allow admins to actually personalize the look of their installation.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
	public function setCSS(Request $request)
	{
		$request->validate(['css' => 'nullable|string']);
		$css = $request->get('css');
		$css = $css == null ? '' : $css;

		if (!Storage::disk('dist')->put('user.css', $css)) {
			Logs::error(__METHOD__, __LINE__, 'Could not save css.');

			return 'false';
		}

		return 'true';
	}

	/**
	 * Return ALL the settings. This is not filtered!
	 * Fortunately this is behind an admin middlewear.
	 * This is used in the advanced settings part.
	 *
	 * @return Collection
	 */
	public function getAll()
	{
		return Configs::orderBy('cat', 'ASC')->get();
	}

	/**
	 * Get a list of settings and save them in the database
	 * if the associated key exists.
	 *
	 * @param Request $request
	 *
	 * @return string
	 */
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
