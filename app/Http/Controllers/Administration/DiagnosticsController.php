<?php

namespace App\Http\Controllers\Administration;

use App\Actions\Diagnostics\Configuration;
use App\Actions\Diagnostics\Errors;
use App\Actions\Diagnostics\Info;
use App\Actions\Diagnostics\Space;
use App\Actions\InstallUpdate\CheckUpdate;
use App\Constants\AccessPermissionConstants as APC;
use App\Contracts\Exceptions\LycheeException;
use App\DTO\DiagnosticInfo;
use App\Exceptions\Internal\FrameworkException;
use App\Exceptions\ModelDBException;
use App\Exceptions\UnauthorizedException;
use App\Models\AccessPermission;
use App\Models\Configs;
use App\Policies\AlbumQueryPolicy;
use App\Policies\SettingsPolicy;
use Carbon\Exceptions\InvalidTimeZoneException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use function Safe\json_encode;

class DiagnosticsController extends Controller
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

		$errors = $collectErrors->get(config('app.skip_diagnostics_checks') ?? []);
		$infos = $authorized ? $collectInfo->get() : [self::ERROR_MSG];
		$configs = $authorized ? $collectConfig->get() : [self::ERROR_MSG];

		return new DiagnosticInfo($errors, $infos, $configs, $checkUpdate->getCode());
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
		} catch (BindingResolutionException $e) {
			throw new FrameworkException('Laravel\'s view component', $e);
		}
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

	public function getFullAccessPermissions(): View
	{
		if (!$this->isAuthorized() && config('app.debug') !== true) {
			throw new UnauthorizedException();
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
				'title',
			])
			->whereNull('user_id')
			->orderBy(APC::BASE_ALBUM_ID)
			->get();

		$data2 = resolve(AlbumQueryPolicy::class)->getComputedAccessPermissionSubQuery()
			->joinSub(
				DB::table('base_albums')
					->select(['id', 'title']),
				'base_albums',
				'base_albums.id',
				'=',
				APC::BASE_ALBUM_ID
			)
			->addSelect('title')
			->groupBy('title')
			->orderBy(APC::BASE_ALBUM_ID)
			->get();

		return view('access-permissions', ['data1' => json_encode($data1, JSON_PRETTY_PRINT), 'data2' => json_encode($data2, JSON_PRETTY_PRINT)]);
	}
}
