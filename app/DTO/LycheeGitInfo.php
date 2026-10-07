<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2017-2018 Tobias Reich
 * Copyright (c) 2018-2026 LycheeOrg.
 */

namespace App\DTO;

/**
 * Description of the local git checkout, shared by Diagnostics and the Maintenance update page.
 * - info: "<branch> (<commit>)", or "<version> (<commit>)" on a detached HEAD,
 * - extra: how far behind master we are (empty on a detached HEAD).
 */
final readonly class LycheeGitInfo
{
	public function __construct(
		public string $info,
		public string $extra,
	) {
	}

	public function toString(): string
	{
		return $this->extra === '' ? $this->info : $this->info . ' -- ' . $this->extra;
	}
}
