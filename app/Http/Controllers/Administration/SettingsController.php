<?php

namespace App\Http\Controllers\Administration;

use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\InsufficientFilesystemPermissions;
use App\Exceptions\Internal\InvalidConfigOption;
use App\Exceptions\Internal\QueryBuilderException;
use App\Exceptions\ModelDBException;
use App\Http\Requests\Settings\GetSetAllSettingsRequest;
use App\Http\Requests\Settings\SetAlbumDecorationRequest;
use App\Http\Requests\Settings\SetCSSSettingRequest;
use App\Http\Requests\Settings\SetDefaultLicenseSettingRequest;
use App\Http\Requests\Settings\SetDropboxKeySettingRequest;
use App\Http\Requests\Settings\SetImageOverlaySettingRequest;
use App\Http\Requests\Settings\SetJSSettingRequest;
use App\Http\Requests\Settings\SetLangSettingRequest;
use App\Http\Requests\Settings\SetLayoutSettingRequest;
use App\Http\Requests\Settings\SetLocationDecodingSettingRequest;
use App\Http\Requests\Settings\SetLocationShowPublicSettingRequest;
use App\Http\Requests\Settings\SetLocationShowSettingRequest;
use App\Http\Requests\Settings\SetMapDisplayPublicSettingRequest;
use App\Http\Requests\Settings\SetMapDisplaySettingRequest;
use App\Http\Requests\Settings\SetMapIncludeSubAlbumsSettingRequest;
use App\Http\Requests\Settings\SetMapProviderSettingRequest;
use App\Http\Requests\Settings\SetNewPhotosNotificationSettingRequest;
use App\Http\Requests\Settings\SetNSFWVisibilityRequest;
use App\Http\Requests\Settings\SetPublicSearchSettingRequest;
use App\Http\Requests\Settings\SetSmartAlbumVisibilityRequest;
use App\Http\Requests\Settings\SetSortingSettingsRequest;
use App\Models\AccessPermission;
use App\Models\Configs;
use App\SmartAlbums\BaseSmartAlbum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class SettingsController extends Controller
{
	/**
	 * Define the default sorting type.
	 *
	 * @param SetSortingSettingsRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 */
	public function setSorting(SetSortingSettingsRequest $request): void
	{
		Configs::set('sorting_photos_col', $request->photoSortingColumn());
		Configs::set('sorting_photos_order', $request->photoSortingOrder());
		Configs::set('sorting_albums_col', $request->albumSortingColumn());
		Configs::set('sorting_albums_order', $request->albumSortingOrder());
	}

	/**
	 * Set the lang used by the Lychee installation.
	 *
	 * @param SetLangSettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 */
	public function setLang(SetLangSettingRequest $request): void
	{
		Configs::set($request->getSettingName(), $request->getSettingValue());
	}

	/**
	 * Set the layout of the albums
	 * 0: squares
	 * 1: flickr justified
	 * 2: flickr unjustified.
	 *
	 * @param SetLayoutSettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 */
	public function setLayout(SetLayoutSettingRequest $request): void
	{
		Configs::set($request->getSettingName(), $request->getSettingValue());
	}

	/**
	 * Set the dropbox key for the API.
	 *
	 * @param SetDropboxKeySettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 */
	public function setDropboxKey(SetDropboxKeySettingRequest $request): void
	{
		Configs::set($request->getSettingName(), $request->getSettingValue());
	}

	/**
	 * Allow public user to use the search function.
	 *
	 * @param SetPublicSearchSettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 * @throws BadRequestException
	 */
	public function setPublicSearch(SetPublicSearchSettingRequest $request): void
	{
		Configs::set($request->getSettingName(), $request->getSettingValue());
	}

	/**
	 * Given a smart album we (un)set the public properties.
	 * TODO: Give possibility to also change the grants_full_photo_access and grants_download.
	 *
	 * @param SetSmartAlbumVisibilityRequest $request
	 *
	 * @return void
	 *
	 * @throws ConfigurationKeyMissingException
	 * @throws ModelDBException
	 */
	public function setSmartAlbumVisibility(SetSmartAlbumVisibilityRequest $request): void
	{
		/** @var BaseSmartAlbum $album */
		$album = $request->album();
		if ($request->is_public() && $album->public_permissions() === null) {
			$access_permissions = AccessPermission::ofPublic();
			$access_permissions->base_album_id = $album->id;
			$access_permissions->save();
		}

		if (!$request->is_public() && $album->public_permissions() !== null) {
			$perm = $album->public_permissions();
			$perm->delete();
		}
	}

	/**
	 * Show NSFW albums by default or not.
	 *
	 * @param SetNSFWVisibilityRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 * @throws BadRequestException
	 */
	public function setNSFWVisible(SetNSFWVisibilityRequest $request): void
	{
		Configs::set($request->getSettingName(), $request->getSettingValue());
	}

	/**
	 * Select the decorations of albums.
	 *
	 * Sub-album and photo counts:
	 * none: no badges.
	 * layers: show folder icon on albums with sub-albums (if any).
	 * album: like 'original' but with number of sub-albums (if any).
	 * photo: show number of photos in album (if any).
	 * all: show number of sub-albums as well as photos.
	 *
	 * Orientation of album decorations. This is only relevant if
	 * both sub-album and photo decorations are shown. These are simply
	 * the options for CSS 'flex-direction':
	 * row: horizontal decorations (photos, albums).
	 * row-reverse: horizontal decorations (albums, photos).
	 * column: vertical decorations (top albums, bottom photos).
	 * column-reverse: vertical decorations (top photos, bottom albums).
	 *
	 * @param SetAlbumDecorationRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 */
	public function setAlbumDecoration(SetAlbumDecorationRequest $request): void
	{
		Configs::set('album_decoration', $request->albumDecoration());
		Configs::set('album_decoration_orientation', $request->albumDecorationOrientation());
	}

	/**
	 * Select the image overlay used:
	 * none: no overlay
	 * desc: description of the photo
	 * date: date of the photo
	 * exif: exif information.
	 *
	 * @param SetImageOverlaySettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 */
	public function setImageOverlayType(SetImageOverlaySettingRequest $request): void
	{
		Configs::set($request->getSettingName(), $request->getSettingValue());
	}

	/**
	 * Define the default license of the pictures.
	 *
	 * @param SetDefaultLicenseSettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 */
	public function setDefaultLicense(SetDefaultLicenseSettingRequest $request): void
	{
		Configs::set($request->getSettingName(), $request->getSettingValue());
	}

	/**
	 * Enable display of photo coordinates on map.
	 *
	 * @param SetMapDisplaySettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 * @throws BadRequestException
	 */
	public function setMapDisplay(SetMapDisplaySettingRequest $request): void
	{
		Configs::set($request->getSettingName(), $request->getSettingValue());
	}

	/**
	 * Enable display of photos on map for public albums.
	 *
	 * @param SetMapDisplayPublicSettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 * @throws BadRequestException
	 */
	public function setMapDisplayPublic(SetMapDisplayPublicSettingRequest $request): void
	{
		Configs::set($request->getSettingName(), $request->getSettingValue());
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
	 * @param SetMapProviderSettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 */
	public function setMapProvider(SetMapProviderSettingRequest $request): void
	{
		Configs::set($request->getSettingName(), $request->getSettingValue());
	}

	/**
	 * Enable display of photos of sub-albums on map.
	 *
	 * @param SetMapIncludeSubAlbumsSettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 * @throws BadRequestException
	 */
	public function setMapIncludeSubAlbums(SetMapIncludeSubAlbumsSettingRequest $request): void
	{
		Configs::set($request->getSettingName(), $request->getSettingValue());
	}

	/**
	 * Enable decoding of GPS data into location names.
	 *
	 * @param SetLocationDecodingSettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 * @throws BadRequestException
	 */
	public function setLocationDecoding(SetLocationDecodingSettingRequest $request): void
	{
		Configs::set($request->getSettingName(), $request->getSettingValue());
	}

	/**
	 * Enable display of location name.
	 *
	 * @param SetLocationShowSettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 * @throws BadRequestException
	 */
	public function setLocationShow(SetLocationShowSettingRequest $request): void
	{
		Configs::set($request->getSettingName(), $request->getSettingValue());
	}

	/**
	 * Enable display of location name for public albums.
	 *
	 * @param SetLocationShowPublicSettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 * @throws BadRequestException
	 */
	public function setLocationShowPublic(SetLocationShowPublicSettingRequest $request): void
	{
		Configs::set($request->getSettingName(), $request->getSettingValue());
	}

	/**
	 * Enable sending of new photos notification emails.
	 *
	 * @param SetNewPhotosNotificationSettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 * @throws BadRequestException
	 */
	public function setNewPhotosNotification(SetNewPhotosNotificationSettingRequest $request): void
	{
		Configs::set($request->getSettingName(), $request->getSettingValue());
	}

	/**
	 * Takes the css input text and put it into `dist/user.css`.
	 * This allows admins to actually personalize the look of their
	 * installation.
	 *
	 * @param SetCSSSettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InsufficientFilesystemPermissions
	 */
	public function setCSS(SetCSSSettingRequest $request): void
	{
		/** @var string $css */
		$css = $request->getSettingValue();
		if (Storage::disk('dist')->put('user.css', $css) === false) {
			if (Storage::disk('dist')->get('user.css') !== $css) {
				throw new InsufficientFilesystemPermissions('Could not save CSS');
			}
		}
	}

	/**
	 * Takes the js input text and put it into `dist/custom.js`.
	 * This allows admins to actually execute custom js code on their
	 * Lychee-Laravel installation.
	 *
	 * @param SetJSSettingRequest $request
	 *
	 * @return void
	 *
	 * @throws InsufficientFilesystemPermissions
	 */
	public function setJS(SetJSSettingRequest $request): void
	{
		/** @var string $js */
		$js = $request->getSettingValue();
		if (Storage::disk('dist')->put('custom.js', $js) === false) {
			if (Storage::disk('dist')->get('custom.js') !== $js) {
				throw new InsufficientFilesystemPermissions('Could not save JS');
			}
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
	public function getAll(GetSetAllSettingsRequest $request): Collection
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
	 * @param GetSetAllSettingsRequest $request
	 *
	 * @return void
	 *
	 * @throws InvalidConfigOption
	 */
	public function saveAll(GetSetAllSettingsRequest $request): void
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
