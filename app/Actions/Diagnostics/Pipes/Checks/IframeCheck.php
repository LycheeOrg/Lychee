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

class IframeCheck implements DiagnosticPipe
{
	/**
	 * We check:
	 * 1. if the X-Frame-Options header is set to 'deny' (SECURITY_HEADER_CSP_FRAME_ANCESTORS is not set = good).
	 * 1. if the session same_site is set to 'none' and session secure is set to false.
	 *
	 * {@inheritDoc}
	 */
	public function handle(array &$data, \Closure $next): array
	{
		// If the X-Frame-Options header is set to 'deny', we don't need to check anything else.
		if (config('secure-headers.x-frame-options') === 'deny') {
			return $next($data);
		}

		$extra = array_map(fn ($allow) => sprintf('Allowing %s to use Lychee in iFrame.', Helpers::censor($allow)), config('secure-headers.csp.frame-ancestors.allow'));
		$extra[] = 'This allows Lychee to be used in iFrame, which is not recommended as it will lower the security of your session cookies.';
		$data[] = DiagnosticData::warn('SECURITY_HEADER_CSP_FRAME_ANCESTORS is set.', self::class, $extra);

		if (config('session.same_site') === 'none' && config('session.secure') === false) {
			$data[] = DiagnosticData::error(
				'Session same_site is set to none, but session secure is set to false.',
				self::class,
				['Set SESSION_SECURE_COOKIE to true in your .env file to solve this issue.']
			);
		}

		return $next($data);
	}
}
