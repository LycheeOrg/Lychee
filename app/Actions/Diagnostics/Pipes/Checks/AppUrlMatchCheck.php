<?php

namespace App\Actions\Diagnostics\Pipes\Checks;

use App\Contracts\DiagnosticPipe;
use Safe\Exceptions\PcreException;
use function Safe\preg_match;

class AppUrlMatchCheck implements DiagnosticPipe
{
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
		if (config('app.url') === 'http://localhost') {
			$data[] = 'Warning: APP_URL is still set to default, this will break access to all your images and assets if you are using Lychee behind a sub-domain.';
		}

		$bad = $this->splitUrl($config_url)[3];

		$censored_bad = $this->censor($bad);
		$censored_app_url = $this->getCensorAppUrl();
		$censored_current = $this->getCensorCurrentUrl();

		if ($bad !== '') {
			$data[] = sprintf(
				'Warning: APP_URL (%s) contains a sub-path (%s). This may impact your WebAuthn authentication.',
				$censored_app_url,
				$censored_bad);
		}

		if (!$this->checkUrlMatchCurrentHost()) {
			$data[] = sprintf(
				'Error: APP_URL (%s) does not match the current url (%s). This will break WebAuthn authentication.',
				$censored_app_url,
				$censored_current,
			);
		}

		$config_url_imgage = config('filesystems.disks.images.url');
		if ($config_url_imgage === '') {
			$data[] = 'Error: LYCHEE_UPLOADS_URL is set and empty. This will prevent images to be displayed. Remove the line from your .env';
		}

		if (($config_url . '/uploads/') === $config_url_imgage && !$this->checkUrlMatchCurrentHost()) {
			$data[] = sprintf(
				'Error: APP_URL (%s) does not match the current url (%s). This will prevent images to be properly displayed.',
				$censored_app_url,
				$censored_current);
		}

		return $next($data);
	}

	/**
	 * Censore a word by replacing half of its character by stars.
	 *
	 * @param string $string
	 *
	 * @return string
	 */
	private function censor(string $string): string
	{
		$strLength = strlen($string);
		if ($strLength === 0) {
			return '';
		}

		$length = $strLength - (int) floor($strLength / 2);
		$start = (int) floor($length / 2);
		$replacement = str_repeat('*', $length);

		return substr_replace($string, $replacement, $start, $length);
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

		return $prefix . $this->censor($good) . $this->censor($bad);
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

		return $prefix . $this->censor($good) . $this->censor($bad);
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
