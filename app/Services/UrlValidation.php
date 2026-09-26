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
		// IPv6 literals are returned by parse_url() wrapped in brackets, e.g. "[::1]".
		if (str_starts_with($host, '[') && str_ends_with($host, ']')) {
			$host = substr($host, 1, -1);
		}

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

			// filter_var() does not decode IPv6 transition addresses that embed an
			// internal IPv4 (NAT64 64:ff9b::/96, RFC 8215 local-use 64:ff9b:1::/48,
			// 6to4 2002::/16, IPv4-mapped ::ffff:0:0/96, IPv4-compatible ::/96). On a
			// host with such routing these resolve to the embedded target, so the
			// check must decode them. We judge the binary form, so alternative textual
			// spellings (compressed, expanded, upper-case, leading zeros) cannot bypass it.
			if ($this->embedsPrivateOrReservedIPv4($ip)) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Whether an IPv6 address embeds a private, reserved or loopback IPv4 via an
	 * address-transition mechanism.
	 *
	 * The test operates on the 16-byte binary form produced by inet_pton(), which
	 * is independent of how the address was written, so equivalent spellings of the
	 * same address (e.g. "64:ff9b::7f00:1", "64:ff9b:0:0:0:0:7f00:1", "64:FF9B::7F00:1")
	 * are all caught.
	 *
	 * @param string $ip
	 *
	 * @return bool
	 */
	private function embedsPrivateOrReservedIPv4(string $ip): bool
	{
		try {
			$bin = inet_pton($ip);
		} catch (NetworkException) {
			return false;
		}

		// Only a 16-byte IPv6 address can wrap an IPv4 address.
		if (strlen($bin) !== 16) {
			return false;
		}

		// RFC 8215 local-use NAT64 (64:ff9b:1::/48): the embedded IPv4 position is not
		// fixed for this range, so reject the whole prefix.
		if (str_starts_with($bin, "\x00\x64\xff\x9b\x00\x01")) {
			return true;
		}

		$high_12 = substr($bin, 0, 12);
		$mapped_prefix = "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\xff\xff"; // ::ffff:0:0/96 (IPv4-mapped)
		$compat_prefix = "\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00\x00"; // ::/96 (IPv4-compatible, deprecated)
		$nat64_prefix = "\x00\x64\xff\x9b\x00\x00\x00\x00\x00\x00\x00\x00";  // 64:ff9b::/96 (well-known NAT64)

		if (in_array($high_12, [$mapped_prefix, $compat_prefix, $nat64_prefix], true)) {
			$embedded = substr($bin, 12, 4); // IPv4 carried in the low 32 bits
		} elseif (str_starts_with($bin, "\x20\x02")) {
			$embedded = substr($bin, 2, 4); // 6to4 (2002::/16) carries the IPv4 in bytes 2..5
		} else {
			return false;
		}

		$embedded_v4 = inet_ntop($embedded);
		if ($embedded_v4 === false) {
			return true; // undecodable embedded address — fail closed
		}

		// Re-run the private/reserved test against the embedded IPv4.
		return filter_var($embedded_v4, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
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