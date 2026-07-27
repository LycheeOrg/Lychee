<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Exceptions\Handlers;

use App\Contracts\Exceptions\Handlers\HttpExceptionHandler;
use App\Exceptions\AdminUserAlreadySetException;
use App\Exceptions\AdminUserRequiredException;
use App\Http\Redirections\ToAdminSetter;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface as HttpException;

/**
 * Class AdminSetterHandler.
 *
 * If the exception {@link AdminUserRequiredException} or
 * {@link AdminUserAlreadySetException} is thrown, this handler
 * redirects to the admin set up page or to the home page.
 *
 * Also see {@link MigrationHandler}.
 */
class AdminSetterHandler implements HttpExceptionHandler
{
	protected bool $toAdminSetter;

	/**
	 * {@inheritDoc}
	 */
	public function check(HttpException $e): bool
	{
		do {
			if ($e instanceof AdminUserRequiredException) {
				$this->toAdminSetter = true;

				return true;
			}
			if ($e instanceof AdminUserAlreadySetException) {
				$this->toAdminSetter = false;

				return true;
			}
			$e = $e->getPrevious();
		} while ($e !== null);

		return false;
	}

	/**
	 * {@inheritDoc}
	 */
	public function renderHttpException(SymfonyResponse $default_response, HttpException $e): SymfonyResponse
	{
		try {
			if ($this->toAdminSetter) {
				$redirect_response = ToAdminSetter::go();
				$content_type = $default_response->headers->get('Content-Type');
				if ($content_type !== null && $content_type !== '') {
					$redirect_response->headers->set('Content-Type', $content_type);
					$content = $default_response->getContent();
					$redirect_response->setContent($content !== false ? $content : null);
				}

				return $redirect_response;
			}

			// The legacy Blade installer's admin-setup form re-renders itself inline
			// with an error message rather than a hard redirect/error page (it wants
			// to keep the user's already-entered values on screen). Historically that
			// happened via a try/catch in SetUpAdminController::create() around
			// CreateInitialAdmin::do(); now that "an admin already exists" is asserted
			// by SetUpAdminRequest::authorize() (which runs before the controller body),
			// the exception never reaches that catch, so we special-case it here.
			if ($e instanceof AdminUserAlreadySetException && request()->routeIs('install-admin')) {
				return $this->renderLegacyInstallerError($e);
			}

			return $default_response;
		} catch (\Throwable) {
			return $default_response;
		}
	}

	private function renderLegacyInstallerError(AdminUserAlreadySetException $e): SymfonyResponse
	{
		$error = $e->getMessage() . '<br>' . ($e->getPrevious()?->getMessage() ?? '');

		return response()->view('install.setup-admin', [
			'title' => 'Lychee-setup-admin',
			'step' => 5,
			'error' => $error,
		]);
	}
}