<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Legacy\V1\Controllers\Administration;

use App\Actions\Diagnostics\Configuration;
use App\Actions\Diagnostics\Errors;
use App\Actions\Diagnostics\Info;
use App\Actions\Diagnostics\Space;
use App\Actions\InstallUpdate\CheckUpdate;
use App\Constants\AccessPermissionConstants as APC;
use App\Contracts\Exceptions\LycheeException;
use App\DTO\DiagnosticData;
use App\Enum\MessageType;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthorizedException;
use App\Legacy\V1\DTO\DiagnosticInfo;
use App\Models\AccessPermission;
use App\Models\Configs;
use App\Policies\AlbumQueryPolicy;
use App\Policies\SettingsPolicy;
use Carbon\Exceptions\InvalidTimeZoneException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use function Safe\json_encode;

final class DiagnosticsController extends Controller
{
	public const ERROR_MSG = 'You must have administrator rights to see this.';

	/**
	 * @throws ModelDBException
	 */
	private function isAuthorized(): bool
	{
		return Gate::check(SettingsPolicy::CAN_SEE_DIAGNOSTICS, Configs::class);
	}

	/**
	 * This function return the Diagnostic data as an JSON array.
	 * should be used for AJAX request.
	 *
	 * @return DiagnosticInfo
	 *
	 * @throws LycheeException
	 * @throws InvalidTimeZoneException
	 */
	public function get(): DiagnosticInfo
	{
		$collectErrors = resolve(Errors::class);
		$collectInfo = resolve(Info::class);
		$collectConfig = resolve(Configuration::class);
		$checkUpdate = resolve(CheckUpdate::class);

		$authorized = $this->isAuthorized();

		$errors = $this->formatErrors($collectErrors->get(config('app.skip_diagnostics_checks') ?? []));
		$infos = $authorized ? $collectInfo->get() : [self::ERROR_MSG];
		$configs = $authorized ? $collectConfig->get() : [self::ERROR_MSG];

		return new DiagnosticInfo($errors, $infos, $configs, $checkUpdate->getCode());
	}

	/**
	 * Format the block.
	 *
	 * @param DiagnosticData[] $array
	 *
	 * @return string[] list of messages
	 */
	private function formatErrors(array $array): array
	{
		$ret = [];
		foreach ($array as $elem) {
			$prefix = match ($elem->type) {
				MessageType::ERROR => 'Error: ',
				MessageType::WARNING => 'Warning: ',
				default => 'Info: ',
			};
			$ret[] = $prefix . $elem->message;
			foreach ($elem->details as $detail) {
				// @codeCoverageIgnoreStart
				$ret[] = '         ' . $detail;
				// @codeCoverageIgnoreEnd
			}
		}

		return $ret;
	}

	/**
	 * Return the diagnostic information as a page.
	 *
	 * @return View
	 *
	 * @throws FrameworkException
	 * @throws InvalidTimeZoneException
	 * @throws LycheeException
	 */
	public function view(): View
	{
		try {
			return view('diagnostics', $this->get());
			// @codeCoverageIgnoreStart
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s view component', $e);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Return the size used by Lychee.
	 * We now separate this from the initial get() call as this is quite time consuming.
	 *
	 * @return string[] list of messages
	 *
	 * @throws ModelDBException
	 */
	public function getSize(Space $space): array
	{
		return $this->isAuthorized() ? $space->get() : [self::ERROR_MSG];
	}

	/**
	 * Return the table of access permissions currently available on the server.
	 *
	 * @return View
	 */
	public function getFullAccessPermissions(AlbumQueryPolicy $albumQueryPolicy): View
	{
		if (!$this->isAuthorized() && config('app.debug') !== true) {
			// @codeCoverageIgnoreStart
			throw new UnauthorizedException();
			// @codeCoverageIgnoreEnd
		}

		$data1 = AccessPermission::query()
			->join('base_albums', 'base_albums.id', '=', APC::BASE_ALBUM_ID)
			->select([
				APC::BASE_ALBUM_ID,
				APC::IS_LINK_REQUIRED,
				APC::GRANTS_FULL_PHOTO_ACCESS,
				APC::GRANTS_DOWNLOAD,
				APC::GRANTS_EDIT,
				APC::GRANTS_UPLOAD,
				APC::GRANTS_DELETE,
				APC::PASSWORD,
				APC::USER_ID,
				'title',
			])
			->when(
				Auth::check(),
				fn ($q1) => $q1
					->where(APC::USER_ID, '=', Auth::id())
					->orWhere(
						fn ($q2) => $q2->whereNull(APC::USER_ID)
							->whereNotIn(
								'access_permissions.' . APC::BASE_ALBUM_ID,
								fn ($q3) => $q3->select('acc_per.' . APC::BASE_ALBUM_ID)
									->from('access_permissions', 'acc_per')
									->where(APC::USER_ID, '=', Auth::id())
							)
					)
			)
			->when(
				!Auth::check(),
				fn ($q1) => $q1->whereNull(APC::USER_ID)
			)
			->orderBy(APC::BASE_ALBUM_ID)
			->get();

		$query2 = DB::table('base_albums');
		$albumQueryPolicy->joinSubComputedAccessPermissions($query2, 'base_albums.id', 'inner', '', true);
		$data2 = $query2
			->select([
				APC::BASE_ALBUM_ID,
				APC::IS_LINK_REQUIRED,
				APC::GRANTS_FULL_PHOTO_ACCESS,
				APC::GRANTS_DOWNLOAD,
				APC::GRANTS_EDIT,
				APC::GRANTS_UPLOAD,
				APC::GRANTS_DELETE,
				APC::PASSWORD,
				APC::USER_ID,
				'title',
			])
			->orderBy(APC::BASE_ALBUM_ID)
			->get()
			->map(function ($e) {
				// @codeCoverageIgnoreStart
				$e->is_link_required = $e->is_link_required === 1;
				$e->grants_download = $e->grants_download === 1;
				$e->grants_upload = $e->grants_upload === 1;
				$e->grants_delete = $e->grants_delete === 1;
				$e->grants_edit = $e->grants_edit === 1;
				$e->grants_full_photo_access = $e->grants_full_photo_access === 1;

				return $e;
				// @codeCoverageIgnoreEnd
			});

		return view('access-permissions', ['data1' => json_encode($data1, JSON_PRETTY_PRINT), 'data2' => json_encode($data2, JSON_PRETTY_PRINT)]);
	}
}
