<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use App\DTO\DiagnosticData;
use App\Facades\Helpers;
use Safe\Exceptions\PcreException;
use function Safe\preg_match;

class AppUrlMatchCheck implements DiagnosticPipe
{
	public const INVISIBLE_ERROR = '      ';
	public const INVISIBLE_WARNING = '        ';

	/**
	 * We check:
	 * 1. if APP_URL is even set.
	 * 1. that APP_URL correctly match the url of the request.
	 * 2. That APP_URL correctly match the url of the request.
	 * 3. That LYCHEE_UPLOADS_URL is not provided by null
	 * 4. that if APP_URL is default, that the config is also using localhost. (Additional Error in the diagnostics).
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		$config_url = config('app.url');
		$dir_url = config('app.dir_url');
		if (config('app.url') === 'http://localhost') {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::warn('APP_URL is still set to default, this will break access to all your images and assets if you are using Lychee behind a sub-domain.', self::class);
			// @codeCoverageIgnoreEnd
		}

		$bad = $this->splitUrl($config_url)[3];

		$censored_bad = Helpers::censor($bad);
		$censored_app_url = $this->getCensorAppUrl();
		$censored_current = $this->getCensorCurrentUrl();

		if ($bad !== '') {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::error(
				sprintf('APP_URL (%s) contains a sub-path (%s).', $censored_app_url, $censored_bad),
				self::class,
				[
					sprintf('Instead set APP_DIR to (%s) and APP_URL to (%s) in your .env', $censored_bad, $censored_current),
				]
			);
			// @codeCoverageIgnoreEnd
		}

		if ($bad !== '') {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::error(
				sprintf('APP_URL (%s) contains a sub-path (%s).', $censored_app_url, $censored_bad),
				self::class,
				['This may impact your WebAuthn authentication.']
			);
			// @codeCoverageIgnoreEnd
		}

		if (!$this->checkUrlMatchCurrentHost()) {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::error(
				sprintf('APP_URL (%s) does not match the current url (%s).', $censored_app_url, $censored_current),
				self::class,
				['This will break WebAuthn authentication.']
			);
			// @codeCoverageIgnoreEnd
		}

		$config_url_imgage = config('filesystems.disks.images.url');
		if ($config_url_imgage === '') {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::error(
				'LYCHEE_UPLOADS_URL is set and empty. This will prevent images to be displayed. Remove the line from your .env',
				self::class
			);
			// @codeCoverageIgnoreEnd
		}

		if (!str_starts_with($config_url_imgage, '/') && !str_starts_with($config_url_imgage, 'http')) {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::error(
				'LYCHEE_UPLOADS_URL is set but starts with neither a / nor http.',
				self::class,
				['This will prevent images from being displayed. Remove the line from your .env']
			);
			// @codeCoverageIgnoreEnd
		}

		if (($config_url . $dir_url . '/uploads') === $config_url_imgage && !$this->checkUrlMatchCurrentHost()) {
			// @codeCoverageIgnoreStart
			$data[] = DiagnosticData::error(
				sprintf('APP_URL (%s) does not match the current url (%s).', $censored_app_url, $censored_current),
				self::class,
				['This will prevent images from being properly displayed.']
			);
			// @codeCoverageIgnoreEnd
		}

		return $next($data);
	}

	/**
	 * Split url into 3 parts: http(s), host, path.
	 *
	 * @param string $url
	 *
	 * @return array<int,string>
	 *
	 * @throws PcreException
	 */
	private function splitUrl(string $url): array
	{
		// https://regex101.com/r/u2YAsS/1
		$pattern = '/((?:http|https)\:\/\/)?([^\/]*)(.*)?/';
		$matches = [];
		preg_match($pattern, $url, $matches);

		return $matches;
	}

	/**
	 * Get the censored version of the current URL.
	 *
	 * @return string
	 */
	private function getCensorCurrentUrl(): string
	{
		$current_url = request()->schemeAndHttpHost();
		[$full, $prefix, $good, $bad] = $this->splitUrl($current_url);

		return $prefix . Helpers::censor($good) . Helpers::censor($bad);
	}

	/**
	 * Retrieve censored version of app.url (APP_URL).
	 *
	 * @return string
	 */
	private function getCensorAppUrl(): string
	{
		$config_url = config('app.url');
		[$full, $prefix, $good, $bad] = $this->splitUrl($config_url);

		return $prefix . Helpers::censor($good) . Helpers::censor($bad);
	}

	/**
	 * Check if current Url matches APP_URL.
	 * We need to check against httpHost and with scheme as APP_URL does not necessarily contain the scheme.
	 *
	 * @return bool true if Match
	 */
	private function checkUrlMatchCurrentHost(): bool
	{
		$config_url = config('app.url');

		return in_array($config_url, [request()->httpHost(), request()->schemeAndHttpHost()], true);
	}
}
