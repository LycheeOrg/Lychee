<?php

namespace App\Http\Controllers;

use App\Contracts\Versions\GitHubVersionControl;
use App\DTO\AlbumSortingCriterion;
use App\DTO\PhotoSortingCriterion;
use App\Exceptions\ConfigurationKeyMissingException;
use App\Exceptions\Handler;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\Internal\InvalidOrderDirectionException;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthenticatedException;
use App\Exceptions\VersionControlException;
use App\Facades\Lang;
use App\Http\Requests\Session\LoginRequest;
use App\Legacy\AdminAuthentication;
use App\Metadata\Versions\FileVersion;
use App\ModelFunctions\ConfigFunctions;
use App\Models\Configs;
use App\Models\Logs;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Spatie\Feed\Helpers\FeedContentType;

class SessionController extends Controller
{
	/**
	 * @param ConfigFunctions      $configFunctions
	 * @param GitHubVersionControl $gitHubVersion
	 * @param FileVersion          $fileVersion,
	 * @param Repository           $configRepository
	 */
	public function __construct(
		private ConfigFunctions $configFunctions,
		private GitHubVersionControl $gitHubVersion,
		private FileVersion $fileVersion,
		private Repository $configRepository,
	) {
	}

	/**
	 * First function being called via AJAX.
	 *
	 * @return array
	 *
	 * @throws VersionControlException
	 * @throws ConfigurationKeyMissingException
	 * @throws FrameworkException
	 * @throws ModelDBException
	 * @throws InvalidOrderDirectionException
	 */
	public function init(): array
	{
		try {
			// Return settings
			$return = [];

			if (AdminAuthentication::loginAsAdminIfNotRegistered()) {
				// TODO: Remove this legacy stuff after creating the admin user has become part of the installation routine.
				// If the session is unauthenticated ('user' === null), but grants admin rights nonetheless,
				// the front-end shows the dialog to create an admin account.
				$return['user'] = null;
				$return['rights'] = [
					'is_admin' => true,
					'is_locked' => false,
					'may_upload' => true,
				];
			} else {
				/** @var User|null $user */
				$user = Auth::user();
				$return['user'] = $user?->toArray();
				$return['rights'] = [
					'is_admin' => Gate::check(UserPolicy::IS_ADMIN, User::class),
					'is_locked' => !Gate::check(UserPolicy::CAN_EDIT_SETTINGS, User::class), // the use of the negation should be removed later
					'may_upload' => Gate::check(UserPolicy::CAN_UPLOAD, User::class),
				];
			}

			// Load configuration settings acc. to authentication status
			if ($return['rights']['is_admin'] === true) {
				// Admin rights (either properly authenticated or not registered)
				$return['config'] = $this->configFunctions->admin();
				$return['config']['location'] = base_path('public/');
				$return['config']['lang_available'] = Lang::get_lang_available();
			} elseif ($return['user'] !== null) {
				// Authenticated as non-admin
				$return['config'] = $this->configFunctions->public();
				$return['config']['lang_available'] = Lang::get_lang_available();
			} else {
				// Unauthenticated
				$return['config'] = $this->configFunctions->public();
				if (Configs::getValueAsBool('hide_version_number')) {
					$return['config']['version'] = '';
				}
			}

			// Consolidate sorting attributes
			$return['config']['sorting_albums'] = AlbumSortingCriterion::createDefault()->toArray();
			$return['config']['sorting_photos'] = PhotoSortingCriterion::createDefault()->toArray();
			unset($return['config']['sorting_albums_col']);
			unset($return['config']['sorting_albums_order']);
			unset($return['config']['sorting_photos_col']);
			unset($return['config']['sorting_photos_order']);

			// Add each RSS feed to the configuration
			// The code is taken from Spatie\Feed\resources\views\links.blade.php
			$return['config']['feeds'] = [];
			if (Configs::getValueAsBool('rss_enable')) {
				try {
					/** @var array<string, array{format: ?string, title: ?string}> $feeds */
					$feeds = $this->configRepository->get('feed.feeds', []);
					foreach ($feeds as $name => $feed) {
						$return['config']['rss_feeds'][] = [
							'url' => route("feeds.{$name}"),
							'mimetype' => FeedContentType::forLink($feed['format'] ?? 'atom'),
							'title' => $feed['title'] ?? '',
						];
					}
				} catch (\Throwable $e) {
					// do nothing, but report the exception, if the
					// configuration for the RSS feed cannot be loaded or
					// if the route to any RSS feed or the mime type of any
					// feed cannot be resolved
					Handler::reportSafely($e);
					$return['config']['feeds'] = [];
				}
			}

			// we also return the local
			$return['locale'] = Lang::get_lang();

			$this->fileVersion->hydrate();
			$this->gitHubVersion->hydrate();
			$return['update_json'] = !$this->fileVersion->isUpToDate();
			$return['update_available'] = !$this->gitHubVersion->isUpToDate();

			return $return;
		} catch (ModelDBException $e) {
			$this->logout();
			throw $e;
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s container component', $e);
		}
	}

	/**
	 * Login tentative.
	 *
	 * @param LoginRequest $request
	 *
	 * @return void
	 *
	 * @throws UnauthenticatedException
	 * @throws ModelDBException
	 */
	public function login(LoginRequest $request): void
	{
		if (AdminAuthentication::loginAsAdmin($request->username(), $request->password(), $request->ip())) {
			return;
		}

		if (Auth::attempt(['username' => $request->username(), 'password' => $request->password()])) {
			Logs::notice(__METHOD__, __LINE__, 'User (' . $request->username() . ') has logged in from ' . $request->ip());

			return;
		}

		// TODO: We could avoid this separate log entry and let the exception handler to all the logging, if we would add "context" (see Laravel docs) to those exceptions which need it.
		Logs::error(__METHOD__, __LINE__, 'User (' . $request->username() . ') has tried to log in from ' . $request->ip());

		throw new UnauthenticatedException('Unknown user or invalid password');
	}

	/**
	 * Unsets the session values.
	 *
	 * @return void
	 */
	public function logout(): void
	{
		Auth::logout();
		Session::flush();
	}
}
