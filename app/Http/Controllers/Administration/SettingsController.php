<?php

namespace App\Http\Controllers\Administration;

use App\Actions\Settings\SetLogin;
use App\Actions\Settings\UpdateLogin;
use App\Contracts\LycheeException;
use App\Exceptions\InsufficientFilesystemPermissions;
use App\Exceptions\Internal\InvalidConfigOption;
use App\Exceptions\Internal\QueryBuilderException;
use App\Facades\Lang;
use App\Http\Requests\Legacy\SetAdminLoginRequest;
use App\Http\Requests\Settings\SetSortingRequest;
use App\Http\Requests\Settings\SettingRequest;
use App\Http\Requests\User\Self\ChangeLoginRequest;
use App\Models\Configs;
use App\Models\User;
use App\Rules\LicenseRule;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class SettingsController extends Controller
{
	/**
	 * Set the Login information for the admin user (id = 0)
	 * when the latter is not initialized.
	 *
	 * @param SetAdminLoginRequest $request
	 * @param SetLogin             $setLogin
	 *
	 * @return User
	 *
	 * @throws LycheeException
	 * @throws ModelNotFoundException
	 */
	public function setLogin(SetAdminLoginRequest $request, SetLogin $setLogin): User
	{
		$adminUser = $setLogin->do(
			$request->username(),
			$request->password()
		);
		// Update the session with the new credentials of the user.
		// Otherwise, the session is out-of-sync and falsely assumes the user
		// to be unauthenticated upon the next request.
		Auth::login($adminUser);

		return $adminUser;
	}

	/**
	 * Update the Login information of the current user.
	 *
	 * @param ChangeLoginRequest $request
	 * @param UpdateLogin        $updateLogin
	 *
	 * @return User
	 *
	 * @throws LycheeException
	 */
	public function updateLogin(ChangeLoginRequest $request, UpdateLogin $updateLogin): User
	{
		$currentUser = $updateLogin->do(
			$request->username(),
			$request->password(),
			$request->oldPassword(),
			$request->ip()
		);
		// Update the session with the new credentials of the user.
		// Otherwise, the session is out-of-sync and falsely assumes the user
		// to be unauthenticated upon the next request.
		Auth::login($currentUser);

		return $currentUser;
	}

	/**
	 * Define the default sorting type.
	 *
	 * @param SetSortingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 */
	public function setSorting(SetSortingRequest $request): void
	{
		Configs::set('sorting_photos_col', $request->photoSortingColumn());
		Configs::set('sorting_photos_order', $request->photoSortingOrder());
		Configs::set('sorting_albums_col', $request->albumSortingColumn());
		Configs::set('sorting_albums_order', $request->albumSortingOrder());
	}

	/**
	 * Set the lang used by the Lychee installation.
	 *
	 * @param SettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 */
	public function setLang(SettingRequest $request): void
	{
		$validated = $request->validate([
			'lang' => ['required', 'string', Rule::in(Lang::get_lang_available())],
		]);
		Configs::set('lang', $validated['lang']);
	}

	/**
	 * Set the layout of the albums
	 * 0: squares
	 * 1: flickr justified
	 * 2: flickr unjustified.
	 *
	 * @param SettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 */
	public function setLayout(SettingRequest $request): void
	{
		$validated = $request->validate([
			'layout' => ['required', Rule::in([0, 1, 2])],
		]);
		Configs::set('layout', $validated['layout']);
	}

	/**
	 * Set the dropbox key for the API.
	 *
	 * @param SettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 */
	public function setDropboxKey(SettingRequest $request): void
	{
		$validated = $request->validate(['key' => 'present|string|nullable']);
		Configs::set('dropbox_key', $validated['key']);
	}

	/**
	 * Allow public user to use the search function.
	 *
	 * @param SettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 * @throws BadRequestException
	 */
	public function setPublicSearch(SettingRequest $request): void
	{
		$request->validate(['public_search' => 'required|boolean']);
		Configs::set('public_search', (int) $request->boolean('public_search'));
	}

	/**
	 * Show NSFW albums by default or not.
	 *
	 * @param SettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 * @throws BadRequestException
	 */
	public function setNSFWVisible(SettingRequest $request): void
	{
		$request->validate(['nsfw_visible' => 'required|boolean']);
		Configs::set('nsfw_visible', (int) $request->boolean('nsfw_visible'));
	}

	/**
	 * Select the image overlay used:
	 * none: no overlay
	 * desc: description of the photo
	 * date: date of the photo
	 * exif: exif information.
	 *
	 * @param SettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 */
	public function setImageOverlayType(SettingRequest $request): void
	{
		$validated = $request->validate([
			'image_overlay_type' => [
				'required',
				'string',
				Rule::in(['none', 'desc', 'date', 'exif']),
			],
		]);

		Configs::set('image_overlay_type', $validated['image_overlay_type']);
	}

	/**
	 * Define the default license of the pictures.
	 *
	 * @param SettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 */
	public function setDefaultLicense(SettingRequest $request): void
	{
		$validated = $request->validate([
			'license' => ['required', new LicenseRule()],
		]);
		Configs::set('default_license', $validated['license']);
	}

	/**
	 * Enable display of photo coordinates on map.
	 *
	 * @param SettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 * @throws BadRequestException
	 */
	public function setMapDisplay(SettingRequest $request): void
	{
		$request->validate(['map_display' => 'required|boolean']);
		Configs::set('map_display', (int) $request->boolean('map_display'));
	}

	/**
	 * Enable display of photos on map for public albums.
	 *
	 * @param SettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 * @throws BadRequestException
	 */
	public function setMapDisplayPublic(SettingRequest $request): void
	{
		$request->validate(['map_display_public' => 'required|boolean']);
		Configs::set('map_display_public', (int) $request->boolean('map_display_public'));
	}

	/**
	 * Set provider of OSM map tiles.
	 *
	 * This configuration option is not used by the backend itself, but only
	 * by the frontend.
	 * The configured value is transmitted to the frontend as part of the
	 * response for `Session::init`
	 * (cp. {@link \App\Http\Controllers\SessionController::init()}) as the
	 * confidentiality of this configuration option is `public`.
	 *
	 * @param SettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 */
	public function setMapProvider(SettingRequest $request): void
	{
		$request->validate([
			'map_provider' => ['required', 'string', Rule::in([
				'Wikimedia',
				'OpenStreetMap.org',
				'OpenStreetMap.de',
				'OpenStreetMap.fr',
				'RRZE',
			])],
		]);

		Configs::set('map_provider', $request['map_provider']);
	}

	/**
	 * Enable display of photos of sub-albums on map.
	 *
	 * @param SettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 * @throws BadRequestException
	 */
	public function setMapIncludeSubAlbums(SettingRequest $request): void
	{
		$request->validate(['map_include_subalbums' => 'required|boolean']);
		Configs::set('map_include_subalbums', (int) $request->boolean('map_include_subalbums'));
	}

	/**
	 * Enable decoding of GPS data into location names.
	 *
	 * @param SettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 * @throws BadRequestException
	 */
	public function setLocationDecoding(SettingRequest $request): void
	{
		$request->validate(['location_decoding' => 'required|boolean']);
		Configs::set('location_decoding', (int) $request->boolean('location_decoding'));
	}

	/**
	 * Enable display of location name.
	 *
	 * @param SettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 * @throws BadRequestException
	 */
	public function setLocationShow(SettingRequest $request): void
	{
		$request->validate(['location_show' => 'required|boolean']);
		Configs::set('location_show', (int) $request->boolean('location_show'));
	}

	/**
	 * Enable display of location name for public albums.
	 *
	 * @param SettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 * @throws BadRequestException
	 */
	public function setLocationShowPublic(SettingRequest $request): void
	{
		$request->validate(['location_show_public' => 'required|boolean']);
		Configs::set(
			'location_show_public',
			(int) $request->boolean('location_show_public')
		);
	}

	/**
	 * Enable sending of new photos notification emails.
	 *
	 * @param SettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 * @throws BadRequestException
	 */
	public function setNewPhotosNotification(SettingRequest $request): void
	{
		$request->validate(['new_photos_notification' => 'required|boolean']);
		Configs::set(
			'new_photos_notification',
			(int) $request->boolean('new_photos_notification')
		);
	}

	/**
	 * Takes the css input text and put it into `dist/user.css`.
	 * This allows admins to actually personalize the look of their
	 * installation.
	 *
	 * @param SettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InsufficientFilesystemPermissions
	 */
	public function setCSS(SettingRequest $request): void
	{
		$request->validate(['css' => 'present|nullable|string']);
		$css = $request->get('css') ?? '';

		if (Storage::disk('dist')->put('user.css', $css) === false) {
			throw new InsufficientFilesystemPermissions('Could not save CSS');
		}
	}

	/**
	 * Returns ALL settings. This is not filtered!
	 * Fortunately, this is behind an admin middleware.
	 * This is used in the advanced settings part.
	 *
	 * @return Collection
	 *
	 * @throws QueryBuilderException
	 */
	public function getAll(SettingRequest $request): Collection
	{
		return Configs::query()
			->orderBy('cat')
			->orderBy('id')
			->get();
	}

	/**
	 * Get a list of settings and save them in the database
	 * if the associated key exists.
	 *
	 * @param SettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 */
	public function saveAll(SettingRequest $request): void
	{
		$lastException = null;
		foreach ($request->except(['_token', 'function', '/api/Settings::saveAll']) as $key => $value) {
			$value ??= '';
			try {
				Configs::set($key, $value);
			} catch (InvalidConfigOption $e) {
				$lastException = $e;
			}
		}
		if ($lastException !== null) {
			throw $lastException;
		}
	}
}
