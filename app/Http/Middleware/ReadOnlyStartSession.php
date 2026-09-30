<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;

/**
 * Starts the session so the request can read it (authenticated user,
 * unlocked albums), but never writes it back: no garbage collection, no
 * "previous URL" tracking, no session cookie, no save.
 *
 * Meant for image/asset routes, where a gallery page issues one request per
 * thumbnail. It replaces {@see \Illuminate\Session\Middleware\StartSession}
 * on those routes; it deliberately does not extend it, since
 * `withoutMiddleware(StartSession::class)` also strips subclasses. Its place
 * in the stack (before `AuthenticateSession`) comes from
 * {@see \App\Http\Kernel::$middlewarePriority}.
 */
class ReadOnlyStartSession
{
	public function __construct(
		protected SessionManager $manager,
	) {
	}

	public function handle(Request $request, \Closure $next): mixed
	{
		if (($this->manager->getSessionConfig()['driver'] ?? null) === null) {
			return $next($request);
		}

		$session = $this->manager->driver();
		$session->setId($request->cookies->get($session->getName()));
		$session->setRequestOnHandler($request);
		$session->start();
		$request->setLaravelSession($session);

		return $next($request);
	}
}
