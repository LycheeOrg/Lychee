<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Http\Controllers\Admin;

use App\Actions\Diagnostics\Configuration;
use App\Actions\Diagnostics\Errors;
use App\Actions\Diagnostics\Info;
use App\Actions\Diagnostics\Space;
use App\Constants\AccessPermissionConstants as APC;
use App\Http\Requests\Diagnostics\DiagnosticsRequest;
use App\Http\Resources\Diagnostics\ErrorLine;
use App\Http\Resources\Diagnostics\Permissions;
use App\Models\AccessPermission;
use App\Policies\AlbumQueryPolicy;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use function Safe\json_encode;
use function Safe\phpinfo;

class DiagnosticsController extends Controller
{
	/**
	 * Display the errors detected in Lychee.
	 *
	 * @param Errors $errors
	 *
	 * @return array<array-key, \App\Http\Resources\Diagnostics\ErrorLine>
	 */
	public function errors(Errors $errors): array
	{
		return ErrorLine::collect($errors->get());
	}

	/**
	 * Get the space usage.
	 * ! This is slow.
	 *
	 * @param DiagnosticsRequest $_request
	 * @param Space              $space
	 *
	 * @return string[]
	 */
	public function space(DiagnosticsRequest $_request, Space $space)
	{
		return $space->get();
	}

	/**
	 * Get info of the installation.
	 *
	 * @param DiagnosticsRequest $_request
	 * @param Info               $info
	 *
	 * @return string[]
	 */
	public function info(DiagnosticsRequest $_request, Info $info): array
	{
		return $info->get();
	}

	/**
	 * Get the configuration of the installation.
	 *
	 * @param DiagnosticsRequest $_request
	 * @param Configuration      $config
	 *
	 * @return string[]
	 */
	public function config(DiagnosticsRequest $_request, Configuration $config): array
	{
		return $config->get();
	}

	/**
	 * Just call the phpinfo function.
	 * Cannot be tested.
	 *
	 * @param DiagnosticsRequest $_request
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	public function phpinfo(DiagnosticsRequest $_request): void
	{
		phpinfo();
	}

	/**
	 * Return the table of access permissions currently available on the server.
	 *
	 * @return Permissions
	 */
	public function getFullAccessPermissions(DiagnosticsRequest $_request, AlbumQueryPolicy $albumQueryPolicy): Permissions
	{
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
				$e->is_link_required = $e->is_link_required === 1;
				$e->grants_download = $e->grants_download === 1;
				$e->grants_upload = $e->grants_upload === 1;
				$e->grants_delete = $e->grants_delete === 1;
				$e->grants_edit = $e->grants_edit === 1;
				$e->grants_full_photo_access = $e->grants_full_photo_access === 1;

				return $e;
			});

		return new Permissions(json_encode($data1, JSON_PRETTY_PRINT), json_encode($data2, JSON_PRETTY_PRINT));
	}
}
