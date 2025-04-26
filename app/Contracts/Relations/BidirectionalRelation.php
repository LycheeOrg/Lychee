<?php

/**
 * SPDX-License-Identifier: MIT
 * Copyright (c) 2018-2025 LycheeOrg.
 */

namespace App\Contracts\Relations;

interface BidirectionalRelation
{
	public function getForeignMethodName(): string;
}
