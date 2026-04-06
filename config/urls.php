<?php

return [
	'update' => [
		// we need this in case the URL of the project changes
		'git' => [
			'commits' => 'https://api.github.com/repos/LycheeOrg/Lychee/commits',
			'tags' => 'https://api.github.com/repos/LycheeOrg/Lychee/tags',
		],
		'json' => 'https://lycheeorg.dev/update.json',
		'changelogs' => 'https://raw.githubusercontent.com/LycheeOrg/LycheeOrg.github.io/refs/heads/master/docs/releases.md',
	],
	'git' => [
		'pull' => 'https://github.com/LycheeOrg/Lychee.git',
	],
	'advisories' => [
		/*
		|--------------------------------------------------------------------------
		| Security Advisories API URL
		|--------------------------------------------------------------------------
		|
		| The URL used to fetch published security advisories for the Lychee
		| project. The endpoint must return a JSON array compatible with the
		| GitHub Security Advisories API format.
		| Requires header "Accept: application/vnd.github+json".
		*/
		'api_url' => env('ADVISORIES_API_URL', 'https://api.github.com/repos/LycheeOrg/Lychee/security-advisories'),

		/*
		|--------------------------------------------------------------------------
		| Security Advisories Cache TTL (days)
		|--------------------------------------------------------------------------
		|
		| How long (in days) the advisory response is cached before a fresh
		| request is made to the advisory API. Defaults to 1 day.
		*/
		'cache_ttl' => (int) env('ADVISORIES_CACHE_TTL_DAYS', 1),
	],
];