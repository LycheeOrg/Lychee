<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\Http\Requests\Traits;

use App\Actions\Import\ImportPathGuard;
use App\Enum\ImportPathStatus;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Import root of the authenticated user (Feature 115, ADR-115-01).
 *
 * Call {@see HasImportRootTrait::allowsPath()} from `authorize()` only,
 * after the policy check, so that nothing is resolved on disk for users
 * who may not import.
 */
trait HasImportRootTrait
{
	/**
	 * Real import root of the authenticated user, `null` for full access.
	 */
	public function importRoot(): ?string
	{
		/** @var User $user */
		$user = Auth::user();

		return ImportPathGuard::realRoot($user->server_path);
	}

	/**
	 * Whether the authenticated user may access $path (inside or missing below their root).
	 * Refusals are logged without telling the user whether the path exists (TE-115-01).
	 */
	protected function allowsPath(string $path): bool
	{
		$is_allowed = ImportPathGuard::check($path, $this->importRoot()) !== ImportPathStatus::OUTSIDE;
		if (!$is_allowed) {
			Log::notice('import path denied', ['user_id' => Auth::id(), 'path' => $path]);
		}

		return $is_allowed;
	}

	/**
	 * Path to use once allowed: lexically normalised for restricted users, unchanged otherwise.
	 */
	protected function effectivePath(string $path): string
	{
		return $this->importRoot() === null ? $path : ImportPathGuard::normalise($path);
	}
}
