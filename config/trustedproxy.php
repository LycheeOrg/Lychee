<?php

use Symfony\Component\HttpFoundation\Request;

return [
	/*
	 * Set trusted proxy IP addresses.
	 *
	 * Both IPv4 and IPv6 addresses are
	 * supported, along with CIDR notation.
	 *
	 * The "*" character is syntactic sugar
	 * within TrustedProxy to trust any proxy
	 * that connects directly to your server,
	 * a requirement when you cannot know the address
	 * of your proxy (e.g. if using ELB or similar).
	 *
	 */
	'proxies' => env('TRUSTED_PROXIES'), // [<ip addresses>,], '*'

	/*
	 * Which headers to use to detect proxy related data (For, Host, Proto, Port)
	 *
	 * Options include:
	 *
	 * - Illuminate\Http\Request::HEADER_X_FORWARDED_ALL (use all x-forwarded-* headers to establish trust)
	 * - Illuminate\Http\Request::HEADER_FORWARDED (use the FORWARDED header to establish trust)
	 * - Illuminate\Http\Request::HEADER_X_FORWARDED_AWS_ELB (If you are using AWS Elastic Load Balancer)
	 *
	 * - 'HEADER_X_FORWARDED_ALL' (use all x-forwarded-* headers to establish trust)
	 * - 'HEADER_FORWARDED' (use the FORWARDED header to establish trust)
	 * - 'HEADER_X_FORWARDED_AWS_ELB' (If you are using AWS Elastic Load Balancer)
	 *
	 * @link https://symfony.com/doc/current/deployment/proxies.html
	 */
	'headers' => Request::HEADER_X_FORWARDED_FOR |
	Request::HEADER_X_FORWARDED_HOST |
	Request::HEADER_X_FORWARDED_PORT |
	Request::HEADER_X_FORWARDED_PROTO |
	Request::HEADER_X_FORWARDED_AWS_ELB,
];
