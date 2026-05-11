<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Services;

use App\DTO\UrlValidatedDTO;
use App\Repositories\ConfigManager;
use Safe\Exceptions\NetworkException;
use Safe\Exceptions\UrlException;
use function Safe\inet_pton;
use function Safe\parse_url;

class UrlValidation
{
	/**
	 * @param ConfigManager $config_manager
	 * @param \Closure      $dns_get_record defaulted to dns_get_record(string $hostname, int $type = ?, array &$authoritative_name_servers = ?, array &$additional_records = ?, bool $raw = ?): array|false
	 *
	 * @return void
	 */
	public function __construct(
		private ConfigManager $config_manager,
		private \Closure|null $dns_get_record = null,
	) {
		$this->dns_get_record = $dns_get_record ?? \Closure::fromCallable('dns_get_record');
	}

	public function validate(mixed $value): UrlValidatedDTO
	{
		if (!is_string($value)) {
			return UrlValidatedDTO::fromError(
				url: '',
				error: 'is not a string.',
			);
		}

		// Validate we are dealing with a valid URL.
		// Note: This does not check whether the URL is reachable, only that it is syntactically correct.
		if (!filter_var($value, FILTER_VALIDATE_URL)) {
			return UrlValidatedDTO::fromError(
				url: $value,
				error: 'is not a valid URL.',
			);
		}

		try {
			// Get the URL components.
			/** @var array{scheme:string|null,host:string,port:string|int|null} $url */
			$url = parse_url($value);
			// @codeCoverageIgnoreStart
			// This is already filtered by the previous filter_var check, but we catch it here
			// to ensure we handle any unexpected exceptions gracefully.
		} catch (UrlException) {
			return UrlValidatedDTO::fromError(
				url: $value,
				error: 'is not a valid URL.',
			);
		}
		// @codeCoverageIgnoreEnd

		$scheme = $url['scheme'] ?? '';
		$host = $url['host'] ?? '';
		$port = $url['port'] ?? null;

		if (
			$this->config_manager->getValueAsBool('import_via_url_require_https') &&
			$scheme !== 'https'
		) {
			return UrlValidatedDTO::fromError(
				url: $value,
				error: 'must be a valid HTTPS URL.',
			);
		}

		if (!in_array($scheme, ['https', 'http', ''], true)) {
			return UrlValidatedDTO::fromError(
				url: $value,
				error: 'must be a valid HTTP or HTTPS URL.',
			);
		}

		if (
			$this->config_manager->getValueAsBool('import_via_url_forbidden_ports') &&
			$port !== null &&
			!in_array($port, [80, 443], true)
		) {
			return UrlValidatedDTO::fromError(
				url: $value,
				error: 'must use a valid port such as 80 or 443.',
			);
		}

		$resolved_ips = $this->resolveHostToIPs($host);

		if (
			$this->config_manager->getValueAsBool('import_via_url_forbidden_local_ip') &&
			$this->hasPrivateOrReservedIP($resolved_ips)
		) {
			return UrlValidatedDTO::fromError(
				url: $value,
				error: 'must not resolve to a private or reserved IP address.',
			);
		}

		if (
			$this->config_manager->getValueAsBool('import_via_url_forbidden_localhost') &&
			$this->hasLocalhostIP($host, $resolved_ips)
		) {
			return UrlValidatedDTO::fromError(
				url: $value,
				error: 'must not resolve to localhost.',
			);
		}

		return new UrlValidatedDTO(
			url: $value,
			resolved_ip: $resolved_ips[0] ?? null,
			error: null,
		);
	}

	/**
	 * Resolve a hostname to its IP addresses.
	 *
	 * If the host is already an IP address, return it directly.
	 *
	 * @param string $host
	 *
	 * @return string[]
	 */
	private function resolveHostToIPs(string $host): array
	{
		// If the host is already a valid IP, no resolution needed.
		if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
			return [$host];
		}

		$ips = [];

		try {
			// Resolve A records (IPv4).
			$a_records = call_user_func($this->dns_get_record, $host, DNS_A);
			if ($a_records !== false) {
				foreach ($a_records as $record) {
					$ips[] = $record['ip'];
				}
			}

			// Resolve AAAA records (IPv6).
			$aaaa_records = call_user_func($this->dns_get_record, $host, DNS_AAAA);
			if ($aaaa_records !== false) {
				foreach ($aaaa_records as $record) {
					$ips[] = $record['ipv6'];
				}
			}
		} catch (\ErrorException) {
			// DNS resolution failed — return empty array.
			// The hostname checks (e.g. literal "localhost") still apply.
		}

		return $ips;
	}

	/**
	 * Check if any of the resolved IPs are private or reserved.
	 *
	 * @param string[] $ips
	 *
	 * @return bool
	 */
	private function hasPrivateOrReservedIP(array $ips): bool
	{
		foreach ($ips as $ip) {
			if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if the host or any resolved IP is localhost.
	 *
	 * @param string   $host
	 * @param string[] $ips
	 *
	 * @return bool
	 */
	private function hasLocalhostIP(string $host, array $ips): bool
	{
		if (strtolower($host) === 'localhost') {
			return true;
		}

		$loopback_v6 = inet_pton('::1');
		foreach ($ips as $ip) {
			if (str_starts_with($ip, '127.')) {
				return true;
			}

			try {
				$ip = inet_pton($ip);
				if ($ip === $loopback_v6) {
					return true;
				}
			} catch (NetworkException) {
				return true; // If we can't parse the IP, assume it's invalid and potentially localhost.
			}
		}

		return false;
	}
}